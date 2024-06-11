<?php

/**
 * @file
 * Post update hooks for the farm_ui module.
 */

declare(strict_types=1);

/**
 * Install the farm_ui_term module.
 */
function farm_ui_post_update_install_farm_ui_term() {
  if (!\Drupal::service('module_handler')->moduleExists('farm_ui_term')) {
    \Drupal::service('module_installer')->install(['farm_ui_term']);
  }
}

/**
 * Install the farmOS UI Timeline module.
 */
function farm_ui_post_update_install_farm_ui_timeline(&$sandbox = NULL) {
  if (!\Drupal::service('module_handler')->moduleExists('farm_ui_timeline')) {
    \Drupal::service('module_installer')->install(['farm_ui_timeline']);
  }
}
