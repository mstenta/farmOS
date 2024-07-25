<?php

/**
 * @file
 * Post update functions for farm_ui_views module.
 */

use Drupal\views\Entity\View;

/**
 * Enable collapsible_filter views display extender.
 */
function farm_ui_views_post_update_enable_collapsible_filter(&$sandbox = NULL) {

  // Enable the collapsible_filter views display extender.
  $views_settings = \Drupal::configFactory()->getEditable('views.settings');
  $display_extenders = $views_settings->get('display_extenders');

  // Only enable if not already configured.
  if (!isset($display_extenders['collapsible_filter'])) {
    $display_extenders['collapsible_filter'] = 'collapsible_filter';
  }

  $views_settings->set('display_extenders', $display_extenders)->save();
}

/**
 * Install the farmOS CSV Export module.
 */
function farm_ui_views_post_update_install_farm_export_csv(&$sandbox) {
  if (!\Drupal::service('module_handler')->moduleExists('farm_export_csv')) {
    \Drupal::service('module_installer')->install(['farm_export_csv']);
  }
}

/**
 * Move farm_quantity View to farm_log_quantity.
 */
function farm_ui_views_post_update_farm_log_quantity(&$sandbox) {
  $view = View::load('farm_quantity');
  if (empty($view)) {
    return;
  }
  $view->set('id', 'farm_log_quantity');
  $view->save();
}
