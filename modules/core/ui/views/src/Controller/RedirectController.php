<?php

namespace Drupal\farm_ui_views\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Redirect controller.
 *
 * @ingroup farm
 */
class RedirectController extends ControllerBase {

  /**
   * Redirect /quantities to /log-quantities.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a Symfony redirect response.
   */
  public function quantities(): RedirectResponse {
    return new RedirectResponse(Url::fromRoute('view.farm_log_quantity.page')->toString());
  }

}
