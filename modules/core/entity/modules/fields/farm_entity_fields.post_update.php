<?php

/**
 * @file
 * Updates farm_entity_fields module.
 */

/**
 * Install farm_parent module.
 */
function farm_entity_fields_post_update_enable_farm_parent(&$sandbox = NULL) {
  if (!\Drupal::service('module_handler')->moduleExists('farm_parent')) {
    \Drupal::service('module_installer')->install(['farm_parent']);
  }
}

/**
 * Add a convention base field to assets and logs.
 */
function farm_entity_fields_post_update_add_convention_assets_logs(&$sandbox = NULL) {
  $entity_types = ['asset', 'log'];
  $module_name = 'farm_entity_fields';
  $field_name = 'convention';
  $field_info = [
    'type' => 'string',
    'label' => t('Convention'),
    'multiple' => TRUE,
    'hidden' => TRUE,
  ];
  $field_definition = \Drupal::service('farm_field.factory')->baseFieldDefinition($field_info);
  foreach ($entity_types as $entity_type) {
    \Drupal::entityDefinitionUpdateManager()->installFieldStorageDefinition($field_name, $entity_type, $module_name, $field_definition);
  }
}
