<?php

declare(strict_types=1);

namespace Drupal\farm_parent\Form;

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
 * Provides a form for assigning asset parent.
 */
class AssetParentActionForm extends ConfirmFormBase {

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
    return 'asset_parent_action_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    // @todo
    return $this->formatPlural(count($this->entities), 'Are you sure you want to assign parent for this @item?', 'Are you sure you want to assign parents for these @items?', [
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
      $this->entities = $this->tempStoreFactory->get('asset_parent_confirm')->get((string) $this->user->id());
    }

    $this->entityType = $this->entityTypeManager->getDefinition('asset', FALSE);
    if (!$this->entityType || empty($this->entities)) {
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

    $form['parent'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Parents'),
      '#description' => $this->t('Reference parent assets to create a lineal/hierarchical relationship.'),
      '#target_type' => 'asset',
      '#selection_handler' => 'views',
      '#selection_settings' => [
        'view' => [
          'view_name' => 'farm_asset_reference',
          'display_name' => 'entity_reference',
        ],
        'match_operator' => 'CONTAINS',
        'match_limit' => 10,
      ],
      '#tags' => TRUE,
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#required' => TRUE,
    ];

    $form['operation'] = [
      '#type' => 'radios',
      '#title' => $this->t('Append or replace'),
      '#description' => $this->t('Select "Append" if you want to add a parent, but keep the existing asset parents. Select "Replace" if you want to replace the existing asset parent with the ones specified above.'),
      '#options' => [
        'append' => $this->t('Append'),
        'replace' => $this->t('Replace'),
      ],
      '#default_value' => 'append',
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

    // Get submitted parent ids.
    $submitted_parent_ids = array_column($form_state->getValue('parent', []), 'target_id');

    // Update parent on accessible entities.
    $total_count = 0;
    foreach ($accessible_entities as $entity) {
      /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parent_field */
      $parent_field = $entity->get('parent');

      // Save existing values if appending.
      $existing_values = [];
      if ($form_state->getValue('operation') === 'append') {
        $existing_values = array_column($parent_field->getValue(), 'target_id');
      }

      // Empty the field.
      $parent_field->setValue([]);

      $new_values = array_unique(array_merge($existing_values, $submitted_parent_ids));
      foreach ($new_values as $parent_id) {
        $parent_field->appendItem($parent_id);
      }

      // Validate the entity before saving.
      $violations = $entity->validate();
      if ($violations->count() > 0) {
        $this->messenger()->addWarning(
          $this->t('Could not assign parent for <a href=":entity_link">%entity_label</a>: validation failed.',
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

      $entity->save();
      $total_count++;
    }

    // Add warning message for inaccessible entities.
    if (!empty($inaccessible_entities)) {
      $inaccessible_count = count($inaccessible_entities);
      $this->messenger()->addWarning($this->formatPlural($inaccessible_count, 'Could not assign parent for @count @item because you do not have the necessary permissions.', 'Could not assign parent for @count @items because you do not have the necessary permissions.', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    // Add confirmation message.
    if (!empty($total_count)) {
      $this->messenger()->addStatus($this->formatPlural($total_count, 'Assigned parent for @count @item.', 'Assigned parent for @count @items', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    $this->tempStoreFactory->get('asset_parent_confirm')->delete((string) $this->currentUser()->id());
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
