<?php

/**
 * @file
 * Hooks provided by farm_fields.
 *
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * @defgroup farm_fields Farm fields module integrations.
 *
 * Module integrations with the farm_fields module.
 */

/**
 * @defgroup farm_fields_hooks Farm fields hooks
 * @{
 * Hooks that can be implemented by other modules in order to extend farm_fields.
 */

/**
 * Alter field instance definitions created with farm_fields_instance().
 *
 * @param array &$field_instance
 *   The field instance definition.
 */
function hook_farm_fields_instance_alter(&$field_instance) {

  // Alter the $field_instance...
  $field_instance['description'] = 'What areas did this activity take place in?';
}

/**
 * @}
 */
