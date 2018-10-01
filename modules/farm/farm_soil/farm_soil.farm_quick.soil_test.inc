<?php

/**
 * @file
 * Farm soil test quick form.
 */

/**
 * Soil test quick form.
 */
function farm_soil_test_form($form, &$form_state) {

  // Wrapper fieldset.
  $form['soil_test'] = array(
    '#type' => 'fieldset',
    '#title' => t('Record soil test results'),
    '#description' => t('Use this form to record the results of a soil test. A new lab test log will be created.'),
    '#tree' => TRUE,
  );

  // Date select (default to now).
  $form['soil_test']['timestamp'] = array(
    '#type' => 'date_select',
    '#title' => t('Date of sample'),
    '#date_format' => 'M j Y H:i',
    '#date_type' => DATE_FORMAT_UNIX,
    '#date_year_range' => '-10:+3',
    '#default_value' => REQUEST_TIME,
    '#required' => TRUE,
  );

  // Area information fieldset.
  $form['soil_test']['area'] = array(
    '#type' => 'fieldset',
    '#title' => t('Area information'),
  );

  // Area reference.
  $form['soil_test']['area']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Area name'),
    '#description' => t('Enter the name of the area that was sampled. A list of existing area options will appear as you type. If the area does not exist, a new one will be created.'),
    '#autocomplete_path' => 'taxonomy/autocomplete/field_farm_area',
    '#required' => TRUE,
  );

  // Sample points.
  $form['soil_test']['area']['sample_points'] = array(
    '#type' => 'fieldset',
    '#title' => t('Sample points'),
    '#description' => '(placeholder for selecting sample points on a map)',
  );

  // Lab results fieldset.
  $form['soil_test']['results'] = array(
    '#type' => 'fieldset',
    '#title' => t('Lab results'),
  );

  // Lab name.
  $form['soil_test']['lab']['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Lab name'),
    '#description' => t('What laboratory performed the soil test?'),
  );

  // File uploads.
  $form['soil_test']['files'] = array(
    '#type' => 'fieldset',
    '#title' => t('Files'),
    '#description' => '(placeholder for uploading files)',
  );

  // Notes
  $form['soil_test']['notes'] = array(
    '#type' => 'text_format',
    '#title' => t('Notes'),
    '#description' => t('Include any other notes that are relevant to this soil test for future reference.'),
    '#format' => 'farm_format',
  );

  // Submit button.
  $form['soil_test']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create lab test log'),
  );

  // Return the form.
  return $form;
}

/**
 * Soil test quick form validate.
 */
function farm_soil_test_form_validate($form, &$form_state) {

}

/**
 * Soil test quick form submit.
 */
function farm_soil_test_form_submit($form, &$form_state) {

  // Parse the area name, create new one if it doesn't exist.
  $area_name = $form_state['values']['amendment']['area']['name'];
  $areas = farm_area_parse_names($area_name, TRUE);

  // If no areas were found/created, bail with an error.
  if (empty($areas)) {
    drupal_set_message(t('An error occurred while creating/loading areas.'), 'error');
    return;
  }

  // Link the log to the quick form.
  if (function_exists('farm_quick_entity_link')) {
    //farm_quick_entity_link('farm_weather_form', 'log', $log);
  }
}
