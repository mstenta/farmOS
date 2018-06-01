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
