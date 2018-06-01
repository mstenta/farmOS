<?php
/**
 * @file
 * farm_livestock.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function farm_livestock_ctools_plugin_api($module = NULL, $api = NULL) {
  return farm_record_api('ctools', $module, $api);
}

/**
 * Implements hook_views_api().
 */
function farm_livestock_views_api($module = NULL, $api = NULL) {
  return farm_record_api('views', $module, $api);
}

/**
 * Implements hook_default_farm_asset_type().
 */
function farm_livestock_default_farm_asset_type() {
  return farm_record_entity_type_definitions('farm_livestock', 'farm_asset');
}

/**
 * Implements hook_default_log_type().
 */
function farm_livestock_default_log_type() {
  return farm_record_entity_type_definitions('farm_livestock', 'log');
}
