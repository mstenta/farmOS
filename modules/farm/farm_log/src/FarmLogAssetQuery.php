<?php

namespace Drupal\farm_log;

/**
 * Class FarmLogAssetQuery.
 */
class FarmLogAssetQuery extends FarmLogQuery {

  // Set a query tag to identify where this came from.
  public $tag = 'FarmLogAssetQuery';

  // The asset id to search for. This can either be a specific id, or a field
  // alias string from another query (ie: 'mytable.asset_id'). For an example of
  // field alias string usage, see the Views field handler code in
  // farm_movement_handler_relationship_location::query(). If this is omitted,
  // the asset reference table will still be joined in, but no further filtering
  // will be done.
  public $asset_id = NULL;

  /**
   * @inheritDoc
   */
  public function build() {
    parent::build();

    /**
     * Please read the comments in farm_log_query() to understand how this works,
     * and to be aware of the limitations and responsibilities we have in this
     * function with regard to sanitizing query inputs.
     */

    // Ensure $asset_id is valid, because it will be used directly in the query
    // string. This is defensive code. See note about farm_log_query() above.
    if (!is_numeric($this->asset_id) || $this->asset_id < 0) {
      $this->asset_id = db_escape_field($this->asset_id);
    }

    // Join in asset reference field. Use an inner join to exclude logs that do
    // not have any asset references.
    $this->query->innerJoin('field_data_field_farm_asset', $this->prefix . 'fdffa', $this->prefix . "fdffa.entity_type = 'log' AND " . $this->prefix . "fdffa.entity_id = " . $this->prefix . "log.id AND " . $this->prefix . "fdffa.deleted = 0");

    // If an asset ID is specified, only include logs that reference it.
    if (!empty($this->asset_id)) {
      $this->query->where($this->prefix . 'fdffa.field_farm_asset_target_id = ' . $this->asset_id);
    }

    // Return the query object.
    return $this->query;
  }
}
