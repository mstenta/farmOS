<?php

/**
 * @file
 * Post update hooks for the farm_birth module.
 */

/**
 * Override the birth log asset label and description.
 */
function farm_birth_post_update_override_birth_asset_label_description(&$sandbox) {

  // Override the asset field label and description on birth logs to make it
  // clear that it should be used to reference the child assets that were born.
  // This also checks to make sure they haven't been overridden already first.
  /** @var \Drupal\Core\Field\Entity\BaseFieldOverride $config */
  $config = \Drupal::service('entity_field.manager')->getBaseFieldDefinitions('log')['asset']->getConfig('birth');
  if ($config->isNew()) {
    $config->set('label', 'Children');
    $config->set('description', 'Which child assets were born?');
    $config->save();
  }
}
