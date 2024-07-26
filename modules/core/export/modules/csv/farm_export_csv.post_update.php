<?php

/**
 * @file
 * Post update hooks for the farm_export_csv module.
 */

use Drupal\system\Entity\Action;

/**
 * Create quantity csv action.
 */
function farm_export_csv_post_update_quantity_csv_action(&$sandbox) {
  $action = Action::create([
    'id' => 'quantity_csv_action',
    'label' => t('Export CSV'),
    'type' => 'quantity',
    'plugin' => 'entity:csv_action:quantity',
    'configuration' => [],
    'dependencies' => [
      'module' => [
        'farm_export_csv',
        'quantity',
      ],
    ],
  ]);
  $action->save();
}
