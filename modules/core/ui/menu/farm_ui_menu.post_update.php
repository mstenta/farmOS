<?php

/**
 * @file
 * Post update hooks for the farm_ui_menu module.
 */

declare(strict_types=1);

/**
 * Replace Toolbar and Admin Toolbar modules with Navigation.
 */
function farm_ui_menu_post_update_install_navigation() {

  // Uninstall Toolbar and Admin Toolbar.
  if (\Drupal::service('module_handler')->moduleExists('admin_toolbar')) {
    \Drupal::service('module_installer')->uninstall(['admin_toolbar']);
  }
  if (\Drupal::service('module_handler')->moduleExists('toolbar')) {
    \Drupal::service('module_installer')->uninstall(['toolbar']);
  }

  // Install Navigation module.
  if (!\Drupal::service('module_handler')->moduleExists('navigation')) {
    \Drupal::service('module_installer')->install(['navigation']);
  }
}
