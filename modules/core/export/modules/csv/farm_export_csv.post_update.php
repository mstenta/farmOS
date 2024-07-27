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
  $action = Action::create([
    'id' => 'log_quantity_csv_action',
    'label' => t('Export Quantities CSV'),
    'type' => 'log',
    'plugin' => 'farm_export_csv:log_quantity',
    'configuration' => [],
    'dependencies' => [
      'module' => [
        'farm_export_csv',
        'log_quantity',
      ],
    ],
  ]);
  $action->save();
}
