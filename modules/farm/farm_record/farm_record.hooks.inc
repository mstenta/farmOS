<?php

/**
 * @file
 * Farm record hooks.
 */

/**
 * Implements hook_hook_info().
 */
function farm_record_hook_info() {
  $hooks['farm_record_types'] = array(
    'group' => 'farm_record',
  );
  return $hooks;
}

/**
 * Implements hook_ctools_plugin_api().
 */
function farm_record_ctools_plugin_api($module = NULL, $api = NULL) {
  return farm_record_api('ctools', $module, $api);
}

/**
 * Implements hook_views_api().
 */
function farm_record_views_api($module = NULL, $api = NULL) {
  return farm_record_api('views', $module, $api);
}

/**
 * Implements hook_default_farm_asset_type().
 */
function farm_record_default_farm_asset_type() {

  // Get a list of modules that implement hook_farm_record_types() but do not
  // implement this hook.
  $modules = farm_record_modules_without_hook('default_farm_asset_type');

  // Iterate through the modules and build asset types on behalf of them.
  $items = array();
  foreach ($modules as $module) {
    $definitions = farm_record_entity_type_definitions($module, 'farm_asset');
    $items = array_merge($items, $definitions);
  }
  return $items;
}

/**
 * Implements hook_default_log_type().
 */
function farm_record_default_log_type() {

  // Get a list of modules that implement hook_farm_record_types() but do not
  // implement this hook.
  $modules = farm_record_modules_without_hook('default_log_type');

  // Iterate through the modules and build log types on behalf of them.
  $items = array();
  foreach ($modules as $module) {
    $definitions = farm_record_entity_type_definitions($module, 'log');
    $items = array_merge($items, $definitions);
  }
  return $items;
}

/**
 * Helper function for building a list of modules that do not implement a hook.
 *
 * @param string $hook
 *   A hook to look for.
 *
 * @return array
 *   Returns an array of module names that do not implement the hook.
 */
function farm_record_modules_without_hook($hook) {

  // Get a list of modules that implement hook_farm_record_types().
  $modules = module_implements('farm_record_types');

  // Iterate through the modules and see if they implement the hook. If they do
  // not, then add them to the $filtered_modules array.
  $filtered_modules = array();
  foreach ($modules as $module) {
    if (!module_hook($module, $hook)) {
      $filtered_modules[] = $module;
    }
  }
  return $filtered_modules;
}
