<?php

/**
 * @file
 * Post update hooks for the farm_ui_theme module.
 */

declare(strict_types=1);

/**
 * Configure Navigation module.
 */
function farm_ui_theme_post_update_configure_navigation() {

  // Install the Navigation module, if it isn't already.
  if (!\Drupal::service('module_handler')->moduleExists('navigation')) {
    \Drupal::service('module_installer')->install(['navigation']);
  }

  // Use the farmOS logo in the navigation menu.
  $path = \Drupal::service('extension.list.module')->getPath('farm_ui_theme');
  $navigation_settings = \Drupal::configFactory()->getEditable('navigation.settings');
  $navigation_settings->set('logo.provider', 'custom');
  $navigation_settings->set('logo.path', $path . '/logo.png');
  $navigation_settings->save();
}
