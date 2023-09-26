<?php

namespace Drupal\farm_import_csv\Plugin\migrate\process;

use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * This plugin looks for existing asset entities.
 *
 * Lookups are performed on multiple fields to find the asset, in the following
 * order:
 *
 * - UUID
 * - ID Tag
 * - Name
 * - ID (primary key)
 *
 * @codingStandardsIgnoreStart
 *
 * Example usage:
 * @code
 * destination:
 *   plugin: 'entity:log'
 * process:
 *   asset:
 *     plugin: asset_lookup
 *     source: asset
 * @endcode

 * @codingStandardsIgnoreEnd
 *
 * @MigrateProcessPlugin(
 *   id = "asset_lookup",
 *   handle_multiples = TRUE
 * )
 */
class AssetLookup extends EntityLookup {

}
