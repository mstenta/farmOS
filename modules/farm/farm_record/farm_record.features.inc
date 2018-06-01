<?php

/**
 * @file
 * Farm record features functions.
 */

/**
 * Standard helper function for defining API information in
 * hook_ctools_plugin_api() and hook_views_api().
 *
 *  @param string $hook
 *   Set this to 'ctools' or 'views' to specify which hook it is used for.
 * @param string $module
 *   Standard argument for the hooks listed above.
 * @param string $api
 *   Standard argument for the hooks listed above.
 *
 * @return array
 *   Returns API information.
 */
function farm_record_api($hook, $module = NULL, $api = NULL) {

  // If the hook is 'ctools'...
  if ($hook == 'ctools') {
    if ($module == 'field_group' && $api == 'field_group') {
      return array('version' => 1);
    }
    if ($module == 'strongarm' && $api == 'strongarm') {
      return array('version' => 1);
    }
  }

  // Or, if the hook is 'views'...
  elseif ($hook == 'views') {
    return array('api' => '3.0');
  }

  // Otherwise, return an empty array.
  else {
    return array();
  }
}
