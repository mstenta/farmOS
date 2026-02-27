<?php

declare(strict_types=1);

namespace Drupal\farm_farm\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\asset\Entity\AssetInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a form for assigning asset to a farm organization.
 */
class AssetFarmActionForm extends ConfirmFormBase {

  use AutowireTrait;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|null
   */
  protected $entityType;

  /**
   * The assets to update.
   *
   * @var \Drupal\asset\Entity\AssetInterface[]
   */
  protected $entities;

  public function __construct(
    protected PrivateTempStoreFactory $tempStoreFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountInterface $user,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asset_farm_action_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    // @todo Change to "Assign asset(s) to a farm organization"
    return $this->formatPlural(count($this->entities), 'Are you sure you want to assign this @item to a farm?', 'Are you sure you want to assign these @items to a farm?', [
      '@item' => $this->entityType->getSingularLabel(),
      '@items' => $this->entityType->getPluralLabel(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    if ($this->entityType->hasLinkTemplate('collection')) {
      return new Url('entity.' . $this->entityType->id() . '.collection');
    }
    else {
      return new Url('<front>');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Save');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array|RedirectResponse {

    // Check if asset IDs were provided in the asset query param.
    if ($asset_ids = $this->getRequest()->query->all('asset')) {

      // Add each asset the user has view access to.
      $this->entities = array_filter($this->entityTypeManager->getStorage('asset')->loadMultiple($asset_ids), function (AssetInterface $asset) {
        return $asset->access('view', $this->user);
      });
    }
    // Else load entities from the tempStore state.
    else {
      $this->entities = $this->tempStoreFactory->get('asset_farm_confirm')->get((string) $this->user->id());
    }

    $this->entityType = $this->entityTypeManager->getDefinition('asset', FALSE);
    if (is_null($this->entityType) || empty($this->entities)) {
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

    $form['farm'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Farm'),
      '#description' => $this->t('Select the farm to associate the asset(s) with.'),
      '#target_type' => 'organization',
      '#selection_handler' => 'default:organization',
      '#selection_settings' => [
        'sort' => [
          'field' => 'name',
          'direction' => 'asc',
        ],
      ],
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#required' => TRUE,
    ];

    // Delegate to the parent method.
    $form = parent::buildForm($form, $form_state);

    // Remove form description text.
    unset($form['description']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Filter out entities the user doesn't have access to.
    $inaccessible_entities = [];
    $accessible_entities = [];
    foreach ($this->entities as $entity) {
      if (!$entity->access('update', $this->currentUser())) {
        $inaccessible_entities[] = $entity;
        continue;
      }
      $accessible_entities[] = $entity;
    }

    // Get submitted farm ID.
    $submitted_farm_id = $form_state->getValue('farm');

    // Update farm on accessible entities.
    $total_count = 0;
    foreach ($accessible_entities as $entity) {
      /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $farm_field */
      $farm_field = $entity->get('farm');

      // Empty the field.
      $farm_field->setValue([]);
      $farm_field->appendItem($submitted_farm_id);

      // Validate the entity before saving.
      $violations = $entity->validate();
      if ($violations->count() > 0) {
        $this->messenger()->addWarning(
          $this->t('Could not assign farm for <a href=":entity_link">%entity_label</a>: validation failed.',
            [
              ':entity_link' => $entity->toUrl()->setAbsolute()->toString(),
              '%entity_label' => $entity->label(),
            ],
          ),
        );
        foreach ($violations as $violation) {
          $this->messenger()->addWarning($violation->getMessage());
        }
        continue;
      }

      // @todo set revision log message.

      $entity->save();
      $total_count++;
    }

    // Add warning message for inaccessible entities.
    if (!empty($inaccessible_entities)) {
      $inaccessible_count = count($inaccessible_entities);
      $this->messenger()->addWarning($this->formatPlural($inaccessible_count, 'Could not assign farm for @count @item because you do not have the necessary permissions.', 'Could not assign farm for @count @items because you do not have the necessary permissions.', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    // Add confirmation message.
    if (!empty($total_count)) {
      $this->messenger()->addStatus($this->formatPlural($total_count, 'Assigned farm for @count @item.', 'Assigned farm for @count @items', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    $this->tempStoreFactory->get('asset_farm_confirm')->delete((string) $this->currentUser()->id());
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
