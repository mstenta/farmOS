<?php
/**
 * @file
 * farm_soil_test.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function farm_soil_test_ctools_plugin_api($module = NULL, $api = NULL) {
  return farm_record_api('ctools', $module, $api);
}

/**
 * Implements hook_views_api().
 */
function farm_soil_test_views_api($module = NULL, $api = NULL) {
  return farm_record_api('views', $module, $api);
}

/**
 * Implements hook_default_log_type().
 */
function farm_soil_test_default_log_type() {
  return farm_record_entity_type_definitions('farm_soil_test', 'log');
}
