<?php

declare(strict_types=1);

namespace Drupal\farm_timeline\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;

/**
 * Provides a farm timeline render element.
 */
#[RenderElement("farm_timeline")]
class FarmTimeline extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#theme' => 'farm_timeline',
      '#pre_render' => [
        [get_class($this), 'preRenderTimeline'],
      ],
      '#rows' => [],

      // Use the farmOS-timeline.js library.
      // @todo Remove this when farmOS-timeline.js is abandoned.
      '#js' => TRUE,
    ];
  }

  /**
   * Pre-render callback for the timeline render array.
   *
   * @param array $element
   *   A renderable array for the timeline.
   *
   * @return array
   *   The final render array for the timeline.
   */
  public static function preRenderTimeline(array $element): array {

    // Set a timeline ID.
    if (empty($element['#attributes']['id'])) {
      $element['#attributes']['id'] = Html::getUniqueId('timeline');
    }

    // Add the farm-timeline class.
    $element['#attributes']['class'][] = 'farm-timeline';

    // If this is not being rendered as an SVG, then use farmOS-timeline.js.
    // @todo Remove this when farmOS-timeline.js is abandoned.
    if (isset($element['#js']) && $element['#js'] === TRUE) {

      // Add timeline rows.
      $element['#attributes']['data-timeline-rows'] = Json::encode($element['#rows'] ?? []);

      // Attach the farm_timeline_js library.
      $element['#attached']['library'][] = 'farm_timeline/farm_timeline_js';
    }

    return $element;
  }

}
