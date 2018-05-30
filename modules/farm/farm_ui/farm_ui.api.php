<?php

/**
 * @file
 * Hooks provided by farm_ui.
 *
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * @defgroup farm_ui Farm UI module integrations.
 *
 * Module integrations with the farm_ui module.
 */

/**
 * @defgroup farm_ui_hooks Farm UI's hooks
 * @{
 * Hooks that can be implemented by other modules in order to extend
 * farm_ui.
 */

/**
 * Provide a group that entity Views can be sorted into.
 *
 * @return array
 *   Returns an array of group information.
 *   Each element should have a unique key and an array of options, including:
 *     'title' - The title of the group. This is optional. If it not provided
 *       then the Views will not be wrapped in a fieldset.
 *     'weight' - The weight of the group relative to others.
 */
function hook_farm_ui_entity_view_groups() {
  $groups = array(
    'assets' => array(
      'weight' => 98,
    ),
    'logs' => array(
      'weight' => 99,
    ),
    'other' => array(
      'weight' => 100,
    ),
  );
  return $groups;
}

/**
 * Attach Views to an entity page.
 *
 * @param $entity_type
 *   The entity type. Currently supports: 'farm_asset' or 'taxonomy_term'.
 * @param $bundle
 *   The entity bundle.
 * @param $entity
 *   The loaded entity object.
 *
 * @return array
 *   Returns an array of View to attach to taxonomy term pages.
 *   Each element in the array can either be the name of a View,
 *   or an array of options, including:
 *     'name' - the machine name of the View
 *     'display' - which display of the View should be used
 *     'arg' - which argument the term id should be passed to in the View
 *       (this is useful if the View has more than one contextual filter)
 *     'group' - the group to put the View in (options are: assets, logs,
 *       other)
 *     'weight' - the weight of the View in the entity page
 *       (this is useful for changing the order of Views)
 *     'always' - always display, even if there are no View results
 *       (default is FALSE)
 */
function hook_farm_ui_entity_views($entity_type, $bundle, $entity) {

  // If the entity is not a planting asset, bail.
  if (!($entity_type == 'farm_asset' && $bundle == 'planting')) {
    return array();
  }

  // Return a list of Views to include on Plantings.
  return array(

    // Example 1: simple View machine name.
    'farm_activity',

    // Example 2: explicitly set details like display, argument position,
    // and weight.
    array(
      'name' => 'farm_log_input',
      'display' => 'block',
      'arg' => 2,
      'group' => 'logs',
      'weight' => 10,
      'always' => TRUE,
    ),
  );
}

/**
 * Provide action links on specific paths, asset types, and views.
 *
 * @return array
 *   Returns an array of actions and their meta information (see example below).
 */
function hook_farm_ui_actions() {

  // Define farm area actions.
  $actions = array(
    'foo' => array(
      'title' => t('Add a foo log'),
      'href' => 'log/add/farm_foo',
      'paths' => array(
        'farm/asset/%/foo',
      ),
      'assets' => array(
        'bar',
      ),
      'views' => array(
        'foo_view',
      ),
    ),
  );
  return $actions;
}

/**
 * Alter area link in area details created by Farm UI.
 *
 * @param $link
 *   An array with keys for the link 'href' and 'title', which will be used
 *   directly in the l() function.
 * @param $entity_info
 *   Information about the entity type that the link is being built for. This
 *   will contain keys:
 *     - entity_type: The entity type.
 *     - bundle: The entity bundle.
 *     - entity_ids: An array of entity IDs that are extracted from the entity
 *       View results (which may be paged, in which case you only get the
 *       first page).
 */
function hook_farm_area_link_alter(&$link, $entity_info) {
  $link = array(
    'title' => 'New title',
    'href' => 'new-path',
  );
}

/**
 * @}
 */
