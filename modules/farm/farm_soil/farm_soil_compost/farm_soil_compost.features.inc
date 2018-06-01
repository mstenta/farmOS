<?php
/**
 * @file
 * farm_soil_compost.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function farm_soil_compost_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function farm_soil_compost_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_default_farm_asset_type().
 */
function farm_soil_compost_default_farm_asset_type() {
  return farm_record_entity_type_definitions('farm_soil_compost', 'farm_asset');
}
