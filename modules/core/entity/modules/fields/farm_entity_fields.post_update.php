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
 * Install taxonomy term external URI field.
 */
function farm_entity_fields_post_update_add_term_external_uri(&$sandbox) {
  $field_info = [
    'type' => 'uri',
    'label' => t('External URI'),
    'description' => t('Link this term to one or more external URLs or ontology item URIs.'),
    'multiple' => TRUE,
    'weight' => [
      'form' => 80,
      'view' => 80,
    ],
  ];
  $field_definition = \Drupal::service('farm_field.factory')->baseFieldDefinition($field_info);
  \Drupal::entityDefinitionUpdateManager()->installFieldStorageDefinition('external_uri', 'taxonomy_term', 'farm_entity_fields', $field_definition);
}
