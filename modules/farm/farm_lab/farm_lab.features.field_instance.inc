<?php
/**
 * @file
 * farm_lab.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function farm_lab_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'log-farm_lab_test-field_farm_area'.
  $field_instances['log-farm_lab_test-field_farm_area'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => array(),
    'deleted' => 0,
    'description' => 'What areas is this sample being taken from?',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'taxonomy',
        'settings' => array(),
        'type' => 'taxonomy_term_reference_link',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_area',
    'label' => 'Areas',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_select',
      'weight' => 6,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_files'.
  $field_instances['log-farm_lab_test-field_farm_files'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => array(),
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'file',
        'settings' => array(),
        'type' => 'file_table',
        'weight' => 9,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_files',
    'label' => 'Files',
    'required' => 0,
    'settings' => array(
      'description_field' => 1,
      'file_directory' => 'farm/log/lab_test',
      'file_extensions' => '',
      'max_filesize' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'multiupload_filefield_widget',
      'settings' => array(
        'progress_indicator' => 'bar',
      ),
      'type' => 'file_mfw',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_flags'.
  $field_instances['log-farm_lab_test-field_farm_flags'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Add flags to this log to enable better sorting and filtering of records.',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 5,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_flags',
    'label' => 'Flags',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_buttons',
      'weight' => 3,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_geofield'.
  $field_instances['log-farm_lab_test-field_farm_geofield'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'This field is optional. It allows you to store geospatial data along with this observation. For example, if you are taking samples from the field, you can record the points where they were taken from.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'openlayers_geofield',
        'settings' => array(
          'data' => 'full',
          'map_layer_preset' => 'farm_map_geofield_formatter:openlayers_geofield_layer_formatter',
        ),
        'type' => 'openlayers_geofield',
        'weight' => 1,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_geofield',
    'label' => 'Geometry',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'openlayers_geofield',
      'settings' => array(
        'allow_edit' => 1,
        'data_storage' => 'collection',
        'delta_handling' => 'default',
        'feature_types' => array(
          'path' => 'path',
          'point' => 'point',
          'polygon' => 'polygon',
        ),
        'geocoder_field' => 'field_farm_files',
        'geocoder_handler' => 'kml',
        'handler_settings' => array(
          'google' => array(
            'all_results' => 0,
            'biasing' => array(
              'bounds' => '',
              'components' => '',
              'region' => '',
            ),
            'geometry_type' => 'point',
            'reject_results' => array(
              'APPROXIMATE' => 0,
              'GEOMETRIC_CENTER' => 0,
              'RANGE_INTERPOLATED' => 0,
              'ROOFTOP' => 0,
            ),
          ),
        ),
        'openlayers_map' => 'farm_map_geofield',
        'showInputField' => 1,
        'use_geocoder' => 1,
      ),
      'type' => 'openlayers_geofield',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_images'.
  $field_instances['log-farm_lab_test-field_farm_images'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => array(),
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'colorbox',
        'settings' => array(
          'colorbox_caption' => 'auto',
          'colorbox_caption_custom' => '',
          'colorbox_gallery' => 'post',
          'colorbox_gallery_custom' => '',
          'colorbox_image_style' => '',
          'colorbox_multivalue_index' => NULL,
          'colorbox_node_style' => 'thumbnail',
          'colorbox_node_style_first' => 'large',
        ),
        'type' => 'colorbox',
        'weight' => 8,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_images',
    'label' => 'Photos',
    'required' => 0,
    'settings' => array(
      'alt_field' => 0,
      'default_image' => 0,
      'file_directory' => 'farm/log/lab_test',
      'file_extensions' => '',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'multiupload_imagefield_widget',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'bar',
      ),
      'type' => 'image_miw',
      'weight' => 6,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_log_category'.
  $field_instances['log-farm_lab_test-field_farm_log_category'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Use this to organize your logs into categories for easier searching and filtering later.',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'taxonomy',
        'settings' => array(),
        'type' => 'taxonomy_term_reference_link',
        'weight' => 6,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_log_category',
    'label' => 'Log category',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_select',
      'weight' => 11,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_log_owner'.
  $field_instances['log-farm_lab_test-field_farm_log_owner'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Optionally assign this task to one or more people.',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'entityreference',
        'settings' => array(
          'bypass_access' => FALSE,
          'link' => FALSE,
        ),
        'type' => 'entityreference_label',
        'weight' => 7,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_log_owner',
    'label' => 'Assigned to',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_select',
      'weight' => 9,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_notes'.
  $field_instances['log-farm_lab_test-field_farm_notes'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 4,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_notes',
    'label' => 'Notes',
    'required' => 0,
    'settings' => array(
      'text_processing' => 1,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 5,
      ),
      'type' => 'text_textarea',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_lab'.
  $field_instances['log-farm_lab_test-field_farm_lab'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'What laboratory performed the test?',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 3,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_lab',
    'label' => 'Laboratory',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 1,
    ),
  );

  // Exported field_instance: 'log-farm_lab_test-field_farm_quantity'.
  $field_instances['log-farm_lab_test-field_farm_quantity'] = array(
    'bundle' => 'farm_lab_test',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Optionally, use this quantity field to store individual lab quantity measurements.',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'field_collection',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'field_collection_fields',
        'weight' => 2,
      ),
    ),
    'entity_type' => 'log',
    'field_name' => 'field_farm_quantity',
    'label' => 'Quantity',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'field_collection',
      'settings' => array(),
      'type' => 'field_collection_embed',
      'weight' => 13,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Add flags to this log to enable better sorting and filtering of records.');
  t('Areas');
  t('Assigned to');
  t('Files');
  t('Flags');
  t('Laboratory');
  t('Geometry');
  t('Log category');
  t('Notes');
  t('Optionally assign this task to one or more people.');
  t('Optionally, use this quantity field to store individual lab quantity measurements.');
  t('Photos');
  t('Quantity');
  t('This field is optional. It allows you to store geospatial data along with this observation. For example, if you are taking samples from the field, you can record the points where they were taken from.');
  t('Use this to organize your logs into categories for easier searching and filtering later.');
  t('What areas is this sample being taken from?');
  t('What laboratory performed the test?');

  return $field_instances;
}