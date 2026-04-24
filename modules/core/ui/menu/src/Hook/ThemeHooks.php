<?php

declare(strict_types=1);

namespace Drupal\farm_ui_menu\Hook;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Theme hook implementations for farm_ui_menu.
 */
class ThemeHooks {

  use StringTranslationTrait;

  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Implements hook_menu_links_discovered_alter().
   */
  #[Hook('menu_links_discovered_alter')]
  public function menuLinksDiscoveredAlter(&$links) {

    // Move the root system.admin menu link to the farm.base parent.
    if (!empty($links['system.admin'])) {
      $links['system.admin']['parent'] = 'farm.base';
      $links['system.admin']['weight'] = 100;
    }

    // Move the farm.report menu link to the farm.base parent.
    if (!empty($links['farm.report'])) {
      $links['farm.report']['parent'] = 'farm.base';
      $links['farm.report']['weight'] = 90;
    }

    // Move the farm.quick:farm.quick menu link to the farm.base parent.
    if (!empty($links['farm.quick:farm.quick'])) {
      $links['farm.quick:farm.quick']['parent'] = 'farm.base';
    }

    // Move the farm.setup menu link to the farm.base parent.
    if (!empty($links['farm.setup'])) {
      $links['farm.setup']['parent'] = 'farm.base';
      $links['farm.setup']['weight'] = 95;
      // Add a setup menu item for taxonomy.
      if ($this->moduleHandler->moduleExists('taxonomy')) {
        $links['farm.setup.taxonomy'] = [
          'title' => $this->t('Taxonomy'),
          'description' => $this->t('Manage the taxonomy terms used for flagging, categorization and organization of farmOS records.'),
          'parent' => 'farm.setup',
          'route_name' => 'entity.taxonomy_vocabulary.collection',
          'weight' => 50,
        ];
      }
    }
  }

  /**
   * Implements hook_local_tasks_alter().
   */
  #[Hook('local_tasks_alter')]
  public function localTasksAlter(&$local_tasks) {

    // Disable Drupal core revisions local tasks.
    $target_entity_types = [
      'asset',
      'data_stream',
      'log',
      'organization',
      'plan',
      'taxonomy_term',
    ];
    foreach ($target_entity_types as $entity_type) {
      unset($local_tasks['entity.version_history:' . $entity_type . '.version_history']);
    }
    // Remove local tasks provided by core taxonomy module.
    $taxonomy_term_tasks = [
      'entity.taxonomy_term.canonical',
      'entity.taxonomy_term.edit_form',
      'entity.taxonomy_term.delete_form',
    ];
    foreach ($taxonomy_term_tasks as $task_id) {
      unset($local_tasks[$task_id]);
    }
  }

}
