<?php

declare(strict_types=1);

namespace Drupal\farm_setup\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for installing farmOS modules.
 *
 * @ingroup farm
 */
class FarmModulesForm extends FormBase {

  use AutowireTrait;

  /**
   * The package name for farmOS asset type modules.
   *
   * @var string
   */
  const FARM_ASSET_PACKAGE = 'farmOS Assets';

  /**
   * The package name for farmOS log type modules.
   *
   * @var string
   */
  const FARM_LOG_PACKAGE = 'farmOS Logs';

  /**
   * The package name for farmOS quick form modules.
   *
   * @var string
   */
  const FARM_QUICK_PACKAGE = 'farmOS Quick Forms';

  /**
   * The package name for farmOS contrib modules.
   *
   * @var string
   */
  const FARM_CONTRIB_PACKAGE = 'farmOS Contrib';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_modules_form';
  }

  public function __construct(
    protected ModuleExtensionList $moduleExtensionList,
  ) {}

  /**
   * Define farmOS core modules that will appear in the form.
   *
   * @return array
   *   Returns an array of farmOS core modules.
   */
  protected function coreModules() {
    return [

      // Asset types.
      'farm_land' => $this->t('Land assets'),
      'farm_plant' => $this->t('Plant assets'),
      'farm_animal' => $this->t('Animal assets'),
      'farm_equipment' => $this->t('Equipment assets'),
      'farm_structure' => $this->t('Structure assets'),
      'farm_water' => $this->t('Water assets'),
      'farm_material' => $this->t('Material assets'),
      'farm_seed' => $this->t('Seed assets'),
      'farm_product' => $this->t('Product assets'),
      'farm_sensor' => $this->t('Sensor assets'),
      'farm_compost' => $this->t('Compost assets'),
      'farm_group' => $this->t('Group assets'),

      // Defaults land and structure types.
      'farm_land_types' => $this->t('Default land types: Property, Field, Bed, Paddock, Landmark'),
      'farm_structure_types' => $this->t('Default structure types: Building, Greenhouse'),

      // Log types.
      'farm_activity' => $this->t('Activity logs'),
      'farm_observation' => $this->t('Observation logs'),
      'farm_seeding' => $this->t('Seeding logs'),
      'farm_input' => $this->t('Input logs'),
      'farm_harvest' => $this->t('Harvest logs'),
      'farm_maintenance' => $this->t('Maintenance logs'),
      'farm_transplanting' => $this->t('Transplanting logs'),
      'farm_lab_test' => $this->t('Lab test logs'),
      'farm_birth' => $this->t('Birth logs'),
      'farm_medical' => $this->t('Medical logs'),

      // Quantity types.
      'farm_quantity_standard' => $this->t('Standard quantity type'),

      // Roles.
      'farm_manager' => $this->t('Manager role'),
      'farm_viewer' => $this->t('Viewer role'),
      'farm_worker' => $this->t('Worker role'),

      // Inventory.
      'farm_inventory' => $this->t('Inventory management'),

      // Export/import.
      'farm_export_csv' => $this->t('CSV exporter'),
      'farm_import_csv' => $this->t('CSV importer'),
      'farm_export_kml' => $this->t('KML exporter'),
      'farm_import_kml' => $this->t('KML asset importer'),

      // Comments.
      'farm_comment_asset' => $this->t('Asset comments'),
      'farm_comment_log' => $this->t('Log comments'),
      'farm_comment_plan' => $this->t('Plan comments'),

      // Organizations.
      'farm_farm' => $this->t('Farm organizations'),

      // Map layers.
      'farm_map_google' => $this->t('Google Maps map layers: Satellite, Terrain, Roadmap'),
      'farm_map_mapbox' => $this->t('Mapbox map layers: Satellite, Outdoors'),

      // API.
      'farm_api' => $this->t('farmOS API'),
      'farm_api_oauth' => $this->t('farmOS API OAuth2 Server'),
      'farm_api_default_consumer' => $this->t('Default API Consumer'),

      // Localization.
      'farm_l10n' => $this->t('Translation/localization features'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // @todo reorganize into assets, logs, etc

    // Set the form title.
    $form['#title'] = $this->t('Install modules');
    $form['#tree'] = TRUE;

    // Core modules.
    $form['core'] = [
      '#type' => 'details',
      '#title' => $this->t('Core modules'),
      '#open' => TRUE,
    ];

    // Contrib modules.
    $form['contrib'] = [
      '#type' => 'details',
      '#title' => $this->t('Community modules'),
      '#open' => TRUE,
    ];

    // Quick form modules.
    $form['quick'] = [
      '#type' => 'details',
      '#title' => $this->t('Quick form modules'),
      '#open' => TRUE,
    ];

    // Submit button.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'install-modules',
      '#value' => $this->t('Install modules'),
    ];

    // Load module options.
    $modules = $this->moduleOptions();

    // Build checkboxes for module options.
    foreach ($modules as $type => $options) {

      // Hide the fieldset if no modules are found.
      if (empty($options['options'])) {
        $form[$type]['#access'] = FALSE;
        continue;
      }

      // Build checkboxes.
      $form[$type]['modules'] = [
        '#title' => $this->t('farmOS Modules'),
        '#title_display' => 'invisible',
        '#type' => 'container',
      ];

      // Add a checkbox for each module.
      foreach ($options['options'] as $module => $module_info) {
        $form[$type]['modules'][$module] = [
          '#type' => 'checkbox',
          '#title' => $module_info['name'],
          '#description' => $module_info['description'],
          '#default_value' => in_array($module, $options['default']),
        ];
      }

      // Disable checkboxes for modules marked as disabled.
      foreach ($options['disabled'] as $name) {
        $form[$type]['modules'][$name]['#disabled'] = TRUE;
      }
    }
    return $form;
  }

  /**
   * Helper function for building a list of modules to install.
   *
   * @return array
   *   Returns an array with two sub-arrays: `core` and `contrib`. Each of
   *   these includes three sub-arrays: 'options', 'default' and 'disabled'.
   *   All modules should be included in the 'options' array. Default modules
   *   will be selected for installation by default, and disabled modules
   *   cannot have their checkbox changed by users.
   */
  protected function moduleOptions() {

    // Reload the module list.
    $this->moduleExtensionList->reset();

    // Start an array of options for core and contrib modules.
    $options = [
      'core' => [],
      'contrib' => [],
      'quick' => [],
    ];

    // Load information about all modules.
    $all_module_info = $this->moduleExtensionList->getAllAvailableInfo();

    // Iterate through core modules and build options with name and description.
    foreach ($this->coreModules() as $module => $module_name) {
      $options['core']['options'][$module] = [
        'name' => $all_module_info[$module]['name'],
        'description' => $all_module_info[$module]['description'] ?? NULL,
      ];
    }

    // Build contrib module options.
    $contrib_modules = array_filter($all_module_info, function ($module_info) {
      return isset($module_info['package']) && $module_info['package'] === static::FARM_CONTRIB_PACKAGE;
    });
    $options['contrib']['options'] = array_map(function ($module_info) {
      return [
        'name' => $module_info['name'],
        'description' => $module_info['description'] ?? NULL,
      ];
    }, $contrib_modules);

    // Build quick form module options.
    $quick_modules = array_filter($all_module_info, function ($module_info) {
      return isset($module_info['package']) && $module_info['package'] === static::FARM_QUICK_PACKAGE;
    });
    $options['quick']['options'] = array_map(function ($module_info) {
      return [
        'name' => $module_info['name'],
        'description' => $module_info['description'] ?? NULL,
      ];
    }, $quick_modules);

    // Check and disable modules that are installed.
    $all_installed_modules = $this->moduleExtensionList->getAllInstalledInfo();
    foreach (['core', 'contrib', 'quick'] as $option_key) {
      $installed_modules = array_keys(array_intersect_key($options[$option_key]['options'], $all_installed_modules));
      $options[$option_key]['default'] = $installed_modules;
      $options[$option_key]['disabled'] = $installed_modules;
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Load the list of modules that should be installed from form_state.
    $core_modules = array_filter($form_state->getValue(['core', 'modules'], []));
    $contrib_modules = array_filter($form_state->getValue(['contrib', 'modules'], []));
    $quick_modules = array_filter($form_state->getValue(['quick', 'modules'], []));
    $selected_modules = array_merge($core_modules, $contrib_modules, $quick_modules);

    // Filter out installed modules.
    $all_installed_modules = $this->moduleExtensionList->getAllInstalledInfo();
    $to_install = array_diff_key($selected_modules, $all_installed_modules);

    // Bail if no modules need to be installed.
    if (empty($to_install)) {
      return;
    }

    // Assemble the batch operation for installing modules.
    $operations = [];
    foreach ($to_install as $module => $weight) {
      $operations[] = [
        [self::class, 'farmInstallModuleBatch'],
        [$module, $this->moduleExtensionList->getName($module)],
      ];
    }
    $batch = [
      'operations' => $operations,
      'title' => $this->t('Installing farmOS modules'),
      'error_message' => $this->t('The installation has encountered an error.'),
    ];

    batch_set($batch);
  }

  /**
   * Implements callback_batch_operation().
   *
   * Performs batch installation of farmOS modules.
   */
  public static function farmInstallModuleBatch($module, $module_name, &$context) {
    \Drupal::service('module_installer')->install([$module], TRUE);
    $context['results'][] = $module;
    $context['message'] = t('Installed %module module.', ['%module' => $module_name]);
  }

}
