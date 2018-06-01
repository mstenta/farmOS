<?php

/**
 * @file
 * Farm record entity code.
 */

/**
 * Standard helper function for defining entity type definitions for use with
 * hook_default_[entity-type]_type().
 *
 * @param string $module
 *   The module that is providing the entity types in hook_farm_record_types().
 * @param string $entity_type
 *   The entity type.
 *
 * @return array
 *   Returns an array of entity type definitions.
 */
function farm_record_entity_type_definitions($module, $entity_type) {

  // Get all record types provided by this module.
  $record_types = farm_record_types_from_module($module);

  // If this module doesn't provide any entities of this type, return an empty
  // array.
  if (empty($record_types[$entity_type])) {
    return array();
  }

  // Iterate through the entity types and build an entity type definition item
  // for each.
  $items = array();
  foreach ($record_types[$entity_type] as $bundle => $data) {

    // Call out to entity type specific helper functions to generate a basic
    // definition.
    $definition = array();
    switch ($entity_type) {
      case 'farm_asset':
        $definition = farm_record_farm_asset_type_definition($bundle, $data);
        break;
      case 'log':
        $definition = farm_record_log_type_definition($bundle, $data);
        break;
    }

    // If the definition is empty, skip it.
    if (empty($definition)) {
      continue;
    }

    // Build the entity type definition object.
    $items[$bundle] = entity_import($entity_type . '_type', drupal_json_encode($definition));
  }

  return $items;
}

/**
 * Returns a basic asset type definition array.
 *
 * @param string $type
 *   The asset type.
 * @param array $data
 *   Data array for the requested type from farm_record_types().
 *
 * @return array
 *   Returns a basic asset type definitions.
 */
function farm_record_farm_asset_type_definition($type, $data) {
  return array(
    'type' => $type,
    'label' => $data['label'],
    'weight' => 0,
  );
}

/**
 * Returns a basic log type definition array.
 *
 * @return array
 *   Returns a basic log type definitions.
 */
function farm_record_log_type_definition($type, $data) {
  return array(
    'type' => $type,
    'label' => $data['label'],
    'weight' => 0,
    'name_pattern' => $data['log_settings']['name_pattern'],
    'name_edit' => !empty($data['log_settings']['done']) ? 1 : 0,
    'done' => !empty($data['log_settings']['done']) ? 1 : 0,
  );
}
