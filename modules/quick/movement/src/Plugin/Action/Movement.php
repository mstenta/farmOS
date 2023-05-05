<?php

namespace Drupal\farm_quick_movement\Plugin\Action;

use Drupal\farm_quick\Plugin\Action\QuickFormActionBase;

/**
 * Action that moves assets with an activity log.
 *
 * @Action(
 *   id = "movement_action",
 *   label = @Translation("Move assets with an activity log."),
 *   type = "asset",
 *   confirm_form_route_name = "farm.quick.movement"
 * )
 */
class Movement extends QuickFormActionBase {

  /**
   * {@inheritdoc}
   */
  public function getQuckFormId(): string {
    return 'movement';
  }

}
