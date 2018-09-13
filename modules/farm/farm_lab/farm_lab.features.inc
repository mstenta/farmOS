<?php
/**
 * @file
 * farm_lab.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function farm_lab_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function farm_lab_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_default_log_type().
 */
function farm_lab_default_log_type() {
  $items = array();
  $items['farm_lab_test'] = entity_import('log_type', '{
    "type" : "farm_lab_test",
    "label" : "Lab Test",
    "weight" : 0,
    "name_pattern" : "Lab test [log:timestamp:short]",
    "name_edit" : "1",
    "done" : "0",
    "rdf_mapping" : []
  }');
  return $items;
}
