<?php

/**
 * @file
 * Hooks provided by farm_lab.
 *
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * @defgroup farm_lab Farm lab module integrations.
 *
 * Module integrations with the farm_lab module.
 */

/**
 * @defgroup farm_lab_hooks Farm lab's hooks
 * @{
 * Hooks that can be implemented by other modules in order to extend farm_lab.
 */

/**
 * Provide a list of lab test types.
 *
 * @return array
 *   Returns an associative array of translated lab test types.
 */
function hook_farm_lab_test_types() {

  // Provide a "Soil test" lab test type.
  return array(
    'soil' => t('Soil test'),
  );
}

/**
 * @}
 */
