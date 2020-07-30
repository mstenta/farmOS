<?php

/**
 * @file
 * Farm metrics hooks implemented by farm_metrics module.
 */

/**
 * Implements hook_farm_metrics().
 */
function farm_metrics_farm_metrics() {

  // Start an empty metrics array.
  $metrics = array();

  // Load information about farmOS entities.
  $entities = farm_ui_entities();

  // Add metrics for each asset type.
  foreach ($entities['farm_asset'] as $type => $info) {

    // Query the database for a count.
    $count = db_query('SELECT COUNT(*) FROM {farm_asset} WHERE type = :type AND archived = 0', array(':type' => $type))->fetchField();

    // If no assets exist, skip.
    if (empty($count)) {
      continue;
    }

    // Build a metric.
    $metrics[$type] = array(
      'label' => $info['label_plural'],
      'value' => $count,
      'link' => farm_ui_view_page_path($info['view']),
    );
  }

  // If the inventory module is installed, and inventory tracking is enabled
  // for this asset type, query the total inventory count.
  if (!module_exists('farm_inventory')) {
    $inventory_asset_types = farm_inventory_asset_types();
    foreach ($metrics as $type => $metric) {
      if (!in_array($type, $inventory_asset_types)) {
        continue;
      }
      $metrics[$type]['value'] = farm_inventory_asset_type_total($type);
    }
  }

  // Return the metrics.
  return $metrics;
}
