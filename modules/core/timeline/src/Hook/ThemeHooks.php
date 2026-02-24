<?php

declare(strict_types=1);

namespace Drupal\farm_timeline\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theme hook implementations for farm_timeline.
 */
class ThemeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'farm_timeline' => [
        'variables' => [
          'attributes' => [],
          // @todo Remove this when farmOS-timeline.js is abandoned.
          'js' => TRUE,
        ],
      ],
    ];
  }

}
