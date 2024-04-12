<?php

/**
 * @file
 * Post update hooks for the plan module.
 */

/**
 * Install plan_record and plan_record_type entity types.
 */
function plan_post_update_install_plan_record(&$sandbox) {
  \Drupal::entityDefinitionUpdateManager()->installEntityType(
    \Drupal::entityTypeManager()->getDefinition('plan_record_type')
  );
  \Drupal::entityDefinitionUpdateManager()->installEntityType(
    \Drupal::entityTypeManager()->getDefinition('plan_record')
  );
}

/**
 * Remove the plan_record data_table attribute.
 */
function plan_post_update_remove_plan_record_data_table(&$sandbox) {

  // Load existing plan_record entity type and remove the data_table attribute.
  $update_manager = \Drupal::entityDefinitionUpdateManager();
  $plan_record = $update_manager->getEntityType('plan_record');
  $plan_record->set('data_table', NULL);

  // Get the existing field storage definitions. This is a required parameter
  // for EntityDefinitionUpdateManagerInterface::updateFieldableEntityType.
  // We cannot use EntityDefinitionUpdateManagerInterface::updateEntityType.
  $field_storage_definitions = \Drupal::service('entity.last_installed_schema.repository')->getLastInstalledFieldStorageDefinitions('plan_record');

  // Update the entity type.
  $update_manager->updateFieldableEntityType($plan_record, $field_storage_definitions, $sandbox);
}
