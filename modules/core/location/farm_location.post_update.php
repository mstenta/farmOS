<?php

/**
 * @file
 * Post update hooks for the farm_location module.
 */

declare(strict_types=1);

use Drupal\system\Entity\Action;

/**
 * Uninstall system.action.asset_move_action.
 */
function farm_location_post_update_uninstall_asset_move_action(&$sandbox) {
  $config = Action::load('asset_move_action');
  if (!empty($config)) {
    $config->delete();
  }
}

/**
 * Replace views_geojson module with farm_geojson.
 */
function farm_location_post_update_farm_geojson(&$sandbox) {

  /** @var \Drupal\Core\Extension\ModuleHandler $module_handler */
  $module_handler = \Drupal::service('module_handler');

  /** @var \Drupal\Core\Extension\ModuleInstaller $module_installer */
  $module_installer = \Drupal::service('module_installer');

  // Install farm_geojson.
  if (!$module_handler->moduleExists('farm_geojson')) {
    $module_installer->install(['farm_geojson']);
  }

  // Load farm_asset_geojson configuration.
  $config = \Drupal::configFactory()->getEditable('views.view.farm_asset_geojson');

  // Update config dependencies.
  $dependencies = $config->get('dependencies');
  $dependencies['module'] = array_map(function ($module) {
    return ($module == 'views_geojson') ? 'farm_geosjon' : $module;
  }, $dependencies['module']);
  sort($dependencies['module']);
  $config->set('dependencies', $dependencies);

  // Update display and style plugins.
  // @todo

  // Save farm_asset_geojson configuration.
  $config->save();

  // Uninstall views_geojson, if no other modules depend on it.
  // @todo IF NO OTHER MODULES DEPEND ON IT
  if ($module_handler->moduleExists('views_geojson')) {
    $module_installer->uninstall(['views_geojson'], FALSE);
  }

}
