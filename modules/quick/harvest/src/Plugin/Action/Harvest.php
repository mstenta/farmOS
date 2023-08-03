<?php

namespace Drupal\farm_quick_harvest\Plugin\Action;

use Drupal\farm_quick\Plugin\Action\QuickFormActionBase;

/**
 * Action for recording harvests.
 *
 * @Action(
 *   id = "harvest",
 *   label = @Translation("Record harvest"),
 *   type = "asset",
 *   confirm_form_route_name = "farm.quick.harvest"
 * )
 */
class Harvest extends QuickFormActionBase {

  /**
   * {@inheritdoc}
   */
  public function getQuickFormId(): string {
    return 'harvest';
  }

}
