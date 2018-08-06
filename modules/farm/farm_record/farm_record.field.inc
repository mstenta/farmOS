<?php

/**
 * @file
 * Helper functions for generating farmOS record fields.
 */

/**
 * Standard helper function for defining field instance definitions for use
 * with hook_field_default_field_instances().
 *
 * @param string $module
 *   The module that is providing the entity types in hook_farm_record_types().
 *
 * @return array
 *   Returns an array of entity type definitions.
 */
function farm_record_field_instance_definitions($module) {
  $field_instances = array();

  // Get all record types provided by this module.
  $record_types = farm_record_types_from_module($module);

  // Iterate through the record entity types and bundles.
  foreach ($record_types as $entity_type => $record_type) {
    foreach ($record_type as $bundle => $data) {

      // If no fields are defined, skip it.
      if (empty($data['fields'])) {
        continue;
      }

      // Iterate through the fields.
      foreach ($data['fields'] as $field) {

        // Build the field name.
        $field_name = 'field_farm_' . $field;

        // Build the field instance definition.
        $instance_name = $entity_type . '-' . $bundle . '-' . $field_name;
        $field_instances[$instance_name] = farm_fields_instance($entity_type, $bundle, $field_name);
      }
    }
  }

  return $field_instances;
}
