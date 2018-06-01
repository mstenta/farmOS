<?php
/**
 * @file
 * farm_log_sale.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function farm_log_sale_ctools_plugin_api($module = NULL, $api = NULL) {
  return farm_record_api('ctools', $module, $api);
}

/**
 * Implements hook_views_api().
 */
function farm_log_sale_views_api($module = NULL, $api = NULL) {
  return farm_record_api('views', $module, $api);
}

/**
 * Implements hook_default_log_type().
 */
function farm_log_sale_default_log_type() {
  return farm_record_entity_type_definitions('farm_log_sale', 'log');
}
