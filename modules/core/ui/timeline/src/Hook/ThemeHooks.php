<?php

declare(strict_types=1);

namespace Drupal\farm_ui_timeline\Hook;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Url;
use Drupal\farm_log\AssetLogsInterface;

/**
 * Theme hook implementations for farm_ui_timeline.
 */
class ThemeHooks {

  public function __construct(
    protected AssetLogsInterface $assetLogs,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_view().
   */
  #[Hook('asset_view')]
  public function assetView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    /** @var \Drupal\asset\Entity\AssetInterface $entity */

    // If this is not full view mode, bail.
    if ($view_mode != 'full') {
      return;
    }

    // If there are no logs associated with the asset, bail.
    $logs = $this->assetLogs->getLogs($entity);
    if (empty($logs)) {
      return;
    }

    // Render the asset timeline.
    $row_url = Url::fromRoute('farm_ui_timeline.asset_timeline', ['asset' => $entity->id()]);
    $build['asset_timeline'] = [
      '#type' => 'farm_timeline',
      '#js' => TRUE,
      '#rows' => [$row_url->setAbsolute()->toString()],
    ];

  }

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type): array {

    // Position the asset timeline in the top region.
    if ($entity_type == 'asset') {
      return [
        'top' => [
          'asset_timeline',
        ],
      ];
    }

    return [];
  }

}
