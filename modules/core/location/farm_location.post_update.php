<?php

/**
 * @file
 * Post update hooks for the farm_location module.
 */

use Drupal\system\Entity\Action;

/**
 * Delete the asset move action (moved to farm_quick_movement module).
 */
function farm_location_post_update_delete_asset_move_action(&$sandbox) {
  $action = Action::load('asset_move_action');
  $action->delete();
}
