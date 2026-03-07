<?php

declare(strict_types=1);

namespace Drupal\farm_action\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a base confirmation form for entity actions.
 */
abstract class FarmActionConfirmFormBase extends ConfirmFormBase implements FarmActionConfirmFormInterface {

  use AutowireTrait;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|null
   */
  protected $entityType;

  /**
   * The entities to categorize.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface[]
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
  public function getQuestion() {
    // @todo generalize/refromat this message. don't ask "are you sure?".
    return $this->formatPlural(count($this->entities), 'Are you sure you want to categorize this @item?', 'Are you sure you want to categorize these @items?', [
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
    // @todo generalize
    return $this->t('Categorize');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $entity_type = NULL): array|RedirectResponse {

    // Only allow log entities.
    // @todo
    if ($entity_type != 'log') {
      throw new PluginException('Unsupported entity type given when building form to categorize entity');
    }

    // Load the entity type definition.
    $this->entityType = $this->entityTypeManager->getDefinition($entity_type, FALSE);

    // Load saved entities.
    // @todo
    $this->entities = $this->tempStoreFactory->get('entity_categorize_confirm')->get((string) $this->user->id());

    // If there are no entities, or if the entity type definition didn't load,
    // redirect the user to the cancel URL.
    if (!$this->entityType || empty($this->entities)) {
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

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

    // @todo perform downstream logic on accessible entities

    // Add warning message for inaccessible entities.
    if (!empty($inaccessible_entities)) {
      $inaccessible_count = count($inaccessible_entities);
      // @todo
      $this->messenger()->addWarning($this->formatPlural($inaccessible_count, 'Could not categorize @count @item because you do not have the necessary permissions.', 'Could not categorize @count @items because you do not have the necessary permissions.', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    // Add confirmation message.
    if (!empty($total_count)) {
      // @todo
      $this->messenger()->addStatus($this->formatPlural($total_count, 'Categorized @count @item.', 'Categorized @count @items.', [
        '@item' => $this->entityType->getSingularLabel(),
        '@items' => $this->entityType->getPluralLabel(),
      ]));
    }

    // @todo
    $this->tempStoreFactory->get('entity_categorize_confirm')->delete($this->currentUser()->id() . ':' . $this->entityType->id());
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
