<?php
/**
 * @file
 * farmOS modules setup form.
 */

/**
 * Define farmOS modules that will be available to enable during setup.
 *
 * @return array
 *   Returns an array with two sub-arrays:
 *     'default': an array of modules that will be checked by default.
 *     'optional': an array of modules that will be unchecked by default.
 */
function farm_setup_modules() {
  return array(
    'default' => array(
      'farm_log_harvest' => t('Harvest logs'),
      'farm_log_input' => t('Input logs'),
      'farm_crop' => t('Crops'),
      'farm_livestock' => t('Livestock'),
      'farm_livestock_weight' => t('Livestock weight tracking'),
      'farm_equipment' => t('Equipment'),
      'farm_equipment_field' => t('Add "Equipment used" field to logs'),
      'farm_calendar' => t('Calendar of logs'),
      'farm_import' => t('CSV importers for assets and logs'),
      'farm_quick' => t('Quick forms UI'),
      'farm_soil_nrcs' => t('NRCS Soil Survey'),
      'farm_soil_test' => t('Soil test logs'),
      'farm_area_generate' => t('Area generator (for generating parallel beds within an area)'),
      'farm_area_import' => t('Import areas in bulk from a single KML file'),
      'farm_area_types' => t('Default area types: Property, Field, Building, etc'),
      'farm_crop_area_types' => t('Crop area types: Bed and Greenhouse'),
      'farm_livestock_area_types' => t('Livestock area types: Paddock'),
      'farm_water' => t('Water area type'),
      'farm_data_field' => t('Add an arbitrary "data" field to logs and assets'),
      'farm_access_roles' => t('Default roles: Manager, Worker, Viewer'),
      'farm_help' => t('farmOS Help Pages'),
      'farm_tour' => t('farmOS Tours'),
      'farm_api' => t('farmOS API'),
    ),
    'optional' => array(
      'farm_water_test' => t('Water test logs'),
      'farm_soil_compost' => t('Compost'),
      'farm_sensor' => t('Sensor'),
      'farm_sensor_listener' => t('Sensor: Listener'),
      'farm_ledger' => t('Sale and purchase logs (beta)'),
      'farm_quantity_report' => t('Quantity report generator (beta)'),
    ),
  );
}

/**
 * Form callback for the modules setup form.
 */
function farm_setup_modules_form($form, &$form_state) {
  $form = array();

  // Load the list of available modules.
  $modules = farm_setup_modules();

  /**
   * @todo
   * Test batch.
   * Select modules that are already enabled.
   * Do not allow unselecting modules.
   * Wrap entire form in a fieldset.
   * Provide high-level farm-type checkboxes, which auto-select modules:
   *   Row crops
   *   Diversified produce
   *   Livestock
   *   Custom
   * Move checkboxes to a "Customize" fieldset (collapsed).
   * Decide how to handle the hook_install_tasks().
   * hook_update_N() in farm.install to install farm_setup on existing sites
   * Add a list of pre-approved contrib farmOS modules, use system_rebuild_module_data()
   *   to check if they are present, and then show them as optional checkboxes in the setup wizard
   * Need a variable to know whether or not setup was run
   */

  // Build a list of module options.
  $module_options = array_merge($modules['default'], $modules['optional']);

  // Default modules will be selected by default.
  $module_defaults = array_keys($modules['default']);

  // Module checkboxes.
  $form['modules'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Modules'),
    '#description' => t('Select the farmOS modules that you would like to be enabled.'),
    '#options' => $module_options,
    '#default_value' => $module_defaults,
  );

  // Submit button.
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

/**
 * Submit callback for the modules setup form.
 */
function farm_setup_modules_form_submit($form, &$form_state) {

  // Get the selected modules.
  $modules = array_filter($form_state['modules']);

  // Load list of module names from the system.
  $files = system_rebuild_module_data();

  // Start an array of batch operations.
  $operations = array();

  // Add operation to enable selected modules.
  foreach ($modules as $module => $enable) {
    if (!empty($enable)) {
      $operations[] = array('farm_setup_enable_module', array($module, $files[$module]->info['name']));
    }
  }

  // Assemble the Batch API.
  $batch = array(
    'title' => t('Installing optional modules'),
    'operations' => $operations,
  );
  batch_set($batch);
}

/**
 * BatchAPI callback: enable a module.
 *
 * @see farm_setup_modules_form_submit()
 */
function farm_setup_enable_module($module, $module_name, &$context) {
  if (!module_exists($module)) {
    module_enable(array($module));
  }
  $context['message'] = st('Installed %module module.', array('%module' => $module_name));
}
