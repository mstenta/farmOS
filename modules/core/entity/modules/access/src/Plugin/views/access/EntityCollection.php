<?php

declare(strict_types=1);

namespace Drupal\farm_entity_access\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity_access\Access\EntityCollectionAccessCheck;
use Drupal\views\Attribute\ViewsAccess;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Checks access for entity collections.
 */
#[ViewsAccess(
  id: 'farm_entity_collection',
  title: new TranslatableMarkup('Entity Collection'),
  help: new TranslatableMarkup('Access will be granted to users having both the collection permission and at least one view permission for the given entity type.'),
)]
class EntityCollection extends AccessPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = TRUE;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return implode(', ', $this->options['entity_type']);
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();
    $options['entity_type'] = ['default' => []];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Check entity collection access when the view is rendered.
    // Because the current display is not set we cannot check for dynamic
    // view arguments.
    foreach ($this->options['entity_type'] ?? [] as $entity_type) {
      if (!EntityCollectionAccessCheck::checkEntityCollectionAccess($account, $entity_type, NULL)->isAllowed()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    // Alter the view route definition when building routes if the view
    // provides a page.
    foreach ($this->options['entity_type'] ?? [] as $entity_type) {
      $route->setRequirement("_{$entity_type}_collection_access", 'TRUE');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $supported_entity_types = [
      'asset',
      'file',
      'log',
      'organization',
      'plan',
      'quantity',
    ];

    // Build options from each available supported entity type.
    $options = [];
    foreach ($supported_entity_types as $entity_type) {
      if ($this->entityTypeManager->hasDefinition($entity_type)) {
        $options[$entity_type] = $this->entityTypeManager->getDefinition($entity_type)->getLabel();
      }
    }
    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => $options,
      '#multiple' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user.permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

}
