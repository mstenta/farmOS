<?php

declare(strict_types=1);

namespace Drupal\farm_ui_views\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\farm_ui_views\FarmUiViewsHelper;
use Drupal\views\ViewExecutable;

/**
 * Views execution hook implementations for farm_ui_views.
 */
class ViewsExecutionHooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFieldManagerInterface $entityFieldManager,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected ModuleHandlerInterface $moduleHandler,
    protected TimeInterface $time,
  ) {}

  /**
   * Implements hook_views_pre_view().
   */
  #[Hook('views_pre_view')]
  public function viewsPreView(ViewExecutable $view, $display_id, array &$args) {

    // We only want to alter the Views we provide.
    if (!in_array($view->id(), ['farm_asset', 'farm_log', 'farm_log_quantity', 'farm_organization', 'farm_organization_asset', 'farm_organization_log', 'farm_plan', 'farm_farm_plan'])) {
      return;
    }

    // Add field and filter handlers for base fields provided by other modules.
    $base_fields = $this->moduleHandler->invokeAll('farm_ui_views_base_fields', [
      $view->getBaseEntityType()->id(),
    ]);
    foreach ($this->entityFieldManager->getBaseFieldDefinitions($view->getBaseEntityType()->id()) as $field_definition) {
      if (!in_array($field_definition->getName(), $base_fields)) {
        continue;
      }
      FarmUiViewsHelper::addHandlers($view, $display_id, 'field', $field_definition);
      FarmUiViewsHelper::addHandlers($view, $display_id, 'filter', $field_definition);
    }

    // If this is a "By type" display, alter the fields and filters.
    $bundle = FarmUiViewsHelper::getBundleArgument($view, $display_id, $args);
    if (!empty($bundle)) {

      // Remove the type field and filter handlers.
      $view->removeHandler($display_id, 'field', 'type');
      $view->removeHandler($display_id, 'filter', 'type');

      // If the entity type has a bundle_plugin manager, add all of its
      // bundle fields and filters to the page_type view.
      if ($this->entityTypeManager->hasHandler($view->getBaseEntityType()->id(), 'bundle_plugin')) {
        /** @var \Drupal\entity\BundleFieldDefinition[] $bundle_fields */
        $bundle_fields = $this->entityTypeManager->getHandler($view->getBaseEntityType()->id(), 'bundle_plugin')->getFieldDefinitions($bundle);
        foreach (array_reverse($bundle_fields) as $field_definition) {
          FarmUiViewsHelper::addHandlers($view, $display_id, 'field', $field_definition);
          FarmUiViewsHelper::addHandlers($view, $display_id, 'filter', $field_definition);
        }
      }
    }

    // Remove the asset and location filters from the log page_asset display.
    // @todo Make the AssetOrLocationArgument compatible with these filters.
    if ($view->id() == 'farm_log' && $display_id == 'page_asset') {
      $view->removeHandler($display_id, 'filter', 'asset_target_id');
      $view->removeHandler($display_id, 'filter', 'location_target_id');
    }

    // If this is the "Upcoming" or "Late" Logs block display, add a "more" link
    // that points to the default page display with appropriate filters.
    if ($view->id() == 'farm_log' && in_array($display_id, ['block_upcoming', 'block_late'])) {
      $view->display_handler->setOption('use_more', TRUE);
      $view->display_handler->setOption('use_more_always', TRUE);
      $view->display_handler->setOption('link_display', 'custom_url');
      $today = date('Y-m-d', $this->time->getRequestTime());
      if ($display_id == 'block_upcoming') {
        $view->display_handler->setOption('use_more_text', $this->t('View all upcoming logs'));
        $view->display_handler->setOption('link_url', 'logs?status[]=pending&start=' . $today . '&order=timestamp&sort=asc');
      }
      elseif ($display_id == 'block_late') {
        $view->display_handler->setOption('use_more_text', $this->t('View all late logs'));
        $view->display_handler->setOption('link_url', 'logs?status[]=pending&end=' . $today);
      }
    }
  }

  /**
   * Implements hook_views_pre_render().
   */
  #[Hook('views_pre_render')]
  public function viewsPreRender(ViewExecutable $view) {

    // We only want to alter the Views we provide.
    if (!in_array($view->id(), ['farm_asset', 'farm_log', 'farm_log_quantity', 'farm_organization', 'farm_plan', 'farm_farm_plan'])) {
      return;
    }

    // We may set the View page title, but assume not.
    $title = '';

    // If this is the farm_asset View and page_children display, include the
    // asset's name.
    if ($view->id() == 'farm_asset' && $view->current_display == 'page_children') {
      $asset_id = $view->args[0];
      $asset = $this->entityTypeManager->getStorage('asset')->load($asset_id);
      if (!empty($asset)) {
        $title = $this->t('Children of %asset', [
          '%asset' => $asset->label(),
        ]);
      }
    }

    // If this is the farm_asset View and page_location display, include the
    // asset's name.
    if ($view->id() == 'farm_asset' && $view->current_display == 'page_location') {
      $asset_id = $view->args[0];
      $asset = $this->entityTypeManager->getStorage('asset')->load($asset_id);
      if (!empty($asset)) {
        $title = $this->t('Assets in %location', [
          '%location' => $asset->label(),
        ]);
      }
    }

    // If this is the farm_log View and page_asset display, include the asset's
    // name.
    if ($view->id() == 'farm_log' && $view->current_display == 'page_asset') {
      $asset_id = $view->args[0];
      $asset = $this->entityTypeManager->getStorage('asset')->load($asset_id);
      if (!empty($asset)) {
        $title = $asset->label() . ' ' . $view->getBaseEntityType()->getPluralLabel();
      }
    }

    // If this is a "By type" display and a bundle argument is specified, load
    // the bundle label and add it to the title.
    $bundle = FarmUiViewsHelper::getBundleArgument($view, $view->current_display, $view->args);
    if (!empty($bundle)) {
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($view->getBaseEntityType()->id());
      if (!empty($bundles[$bundle])) {
        $title = $view->getTitle() . ': ' . $bundles[$bundle]['label'];
      }
    }

    // If this is the farm_asset/farm_log View and page_term display, include
    // the term's name.
    if (in_array($view->id(), ['farm_asset', 'farm_log']) && $view->current_display == 'page_term') {
      $term_id = $view->args[0];
      $entity_bundle = $view->args[1];
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      if (!empty($term)) {
        $vocabulary = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->load($term->bundle());
        $entity_bundle_label = '';
        if ($entity_bundle != 'all') {
          $bundles = $this->entityTypeBundleInfo->getBundleInfo($view->getBaseEntityType()->id());
          if (!empty($bundles[$entity_bundle])) {
            $entity_bundle_label = $bundles[$entity_bundle]['label'] . ' ' . $view->getBaseEntityType()->getPluralLabel();
          }
        }
        if (!empty($entity_bundle_label)) {
          $title = $this->t('%bundle with %vocab term %term', [
            '%bundle' => $entity_bundle_label,
            '%vocab' => $vocabulary->label(),
            '%term' => $term->label(),
          ]);
        }
        else {
          $title = $this->t('%base_type with %vocab term %term', [
            '%base_type' => $view->getBaseEntityType()->getCollectionLabel(),
            '%vocab' => $vocabulary->label(),
            '%term' => $term->label(),
          ]);
        }
      }
    }

    // Set the title, if so desired.
    if (!empty($title)) {
      $view->setTitle($title);
    }
  }

}
