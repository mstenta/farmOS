<?php

namespace Drupal\farm_ui_guide\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Guide controller.
 *
 * @ingroup farm
 */
class GuideController extends ControllerBase {

  /**
   * Builds the farmOS user guide page.
   *
   * @return array
   *   Render array.
   */
  public function guide() {

    // Start a build array.
    $build = [];

    // @todo parse /docs/guide and build pages
    // @see https://twig.symfony.com/doc/2.x/filters/markdown_to_html.html

    // Return the build array.
    return $build;
  }

}
