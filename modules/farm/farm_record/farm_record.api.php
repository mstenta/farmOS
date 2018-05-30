<?php

/**
 * @file
 * Hooks provided by farm_record.
 *
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * @defgroup farm_record Farm Record module integrations.
 *
 * Module integrations with the farm_record module.
 */

/**
 * @defgroup farm_record_hooks Farm Record's hooks
 * @{
 * Hooks that can be implemented by other modules in order to extend
 * farm_record.
 */

/**
 * Define farmOS-specific record types that the module provides. This is used
 * by farmOS to generate standard UIs and other functionality.
 *
 * @return array
 *   Returns an array of metadata about record types (see example below).
 */
function hook_farm_record_types() {
  $record_types = array(

    // Define farm_asset entity types provided by this module.
    'farm_asset' => array(

      // Plantings:
      'planting' => array(

        // Label
        'label' => t('Planting'),

        // Label (plural)
        'label_plural' => t('Plantings'),

        // View of plantings (optional).
        'view' => 'farm_plantings',
      ),
    ),

    // Define farm_plan entity types provided by this module.
    'farm_plan' => array(

      // Grazing plans:
      'grazing' => array(

        // Label
        'label' => t('Grazing Plan'),

        // Label (plural)
        'label_plural' => t('Grazing Plans'),

        // View of grazing plans (optional).
        'view' => 'farm_grazing_plan',
      ),
    ),

    // Define log entity types provided by this module.
    'log' => array(

      // Seedings:
      'farm_seeding' => array(

        // Label.
        'label' => t('Seeding'),

        // Label (plural).
        'label_plural' => t('Seedings'),

        // View of seedings (optional).
        'view' => 'farm_log_seeding',

        // The specific asset type that these logs apply to (optional).
        // This will add an action link to asset pages for adding a log.
        // It will also limit the asset type that can be referenced by the log.
        // Set this to 'none' if the log does not apply to any asset types.
        // Set it to 'all' if the log can apply to all asset types (this is the
        // default behavior).
        'farm_asset' => 'planting',

        // Set 'areas' to TRUE if the log type can be used on areas.
        // This will add an action link on area pages, add a View to area
        // pages, and will show a link in the area details popup.
        'areas' => TRUE,

        // The position of an asset ID contextual filter argument in the log
        // View, which will be used to filter the logs to only include ones
        // that reference a particular asset. This is used when logs Views are
        // added to asset record pages, to show logs associated with the asset.
        // This is optional, and will default to 1 if omitted.
        'log_view_asset_arg' => 1,

        // Define the weight of this log type relative to others (optional).
        // This will be used to sort the log Views displayed on entities, as
        // well as action links displayed at the top of the page.
        // Best practice for this is to use increments of 10 between -90 and 90,
        // roughly in the order that logs will typically take place with an
        // entity. -100 and 100 should be reserved for special cases where it
        // absolutely needs to be the very first or the very last item.
        /**
         * @see hook_farm_ui_entity_views()
         */
        'weight' => 10,
      ),
    ),

    // Define taxonomy_term vocabularies provided by this module.
    'taxonomy_term' => array(

      // Crops:
      'farm_crops' => array(

        // Label.
        'label' => t('Crop'),

        // Label (plural).
        'label_plural' => t('Crops'),

        // View of crops (optional).
        'view' => 'farm_crops',

        // The specific asset type that these terms apply to (optional).
        'farm_asset' => 'planting',

        // The position of a contextual filter argument corresponding to this
        // taxonomy term in the View of assets that these terms apply to
        // (optional). This will enable the View of assets to be displayed
        // on the term pages, filtered to only show assets tagged with the
        // term being viewed. In most cases, this will be 2 or 3, because asset
        // Views should always have asset location as their first argument.
        'asset_view_arg' => 2,
      ),
    ),
  );
  return $record_types;
}

/**
 * @}
 */
