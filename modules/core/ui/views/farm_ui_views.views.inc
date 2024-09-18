<?php

/**
 * @file
 * Provides Views data for farm_ui_views.module.
 */

/**
 * Implements hook_views_data_alter().
 */
function farm_ui_views_views_data_alter(array &$data) {

  // Use core entity_reference filter plugin for all entity reference fields.
  // @todo Refactor/remove this when the following core issues are resolved.
  // @see https://www.drupal.org/project/drupal/issues/3458099
  // @see https://www.drupal.org/project/drupal/issues/3438054
  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager */
  $entity_field_manager = \Drupal::service('entity_field.manager');
  $entity_reference_field_map = $entity_field_manager->getFieldMapByFieldType('entity_reference');
  foreach ($entity_reference_field_map as $entity_type_id => $fields) {
    foreach ($fields as $field_name => $map) {
      if (!empty($data[$entity_type_id . '__' . $field_name][$field_name . '_target_id']['filter'])) {
        $data[$entity_type_id . '__' . $field_name][$field_name . '_target_id']['filter']['id'] = 'entity_reference';
      }
    }
  }

  // Provide an asset_or_location argument for views of logs.
  if (isset($data['log_field_data'])) {
    $data['log_field_data']['asset_or_location'] = [
      'title' => t('Asset or location'),
      'help' => t('Assets that are referenced by the asset or location field on the log.'),
      'argument' => [
        'id' => 'asset_or_location',
      ],
    ];
  }

  // Provide an asset_taxonomy_term_reference argument for views of assets.
  if (isset($data['asset_field_data'])) {
    $data['asset_field_data']['asset_taxonomy_term_reference'] = [
      'title' => t('Asset Taxonomy Term Reference'),
      'help' => t('Taxonomy Terms that are referenced by the asset.'),
      'argument' => [
        'id' => 'entity_taxonomy_term_reference',
      ],
    ];
  }

  // Provide a log_taxonomy_term_reference argument for views of logs.
  if (isset($data['log_field_data'])) {
    $data['log_field_data']['log_taxonomy_term_reference'] = [
      'title' => t('Log Taxonomy Term Reference'),
      'help' => t('Taxonomy Terms that are referenced by the log.'),
      'argument' => [
        'id' => 'entity_taxonomy_term_reference',
      ],
    ];
  }
}
