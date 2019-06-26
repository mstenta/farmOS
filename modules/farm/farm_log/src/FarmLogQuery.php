<?php

namespace Drupal\farm_log;

/**
 * Class FarmLogQuery.
 */
class FarmLogQuery {

  // The query object.
  protected $query;

  // Set a query tag to identify where this came from.
  public $tag = 'FarmLogQuery';

  // We use an explicit prefix on aliases to avoid potential name conflicts when
  // this query is used as a sub-select inside another query.
  public $prefix = 'ss_';

  // Unix timestamp limiter. Only logs before this time will be included.
  // Defaults to the current time. Set to 0 to load the absolute last.
  public $time = REQUEST_TIME;

  // Whether or not to only show logs that are marked as "done". TRUE will limit
  // to logs that are done, and FALSE will limit to logs that are not done. If
  // this is set to NULL, no filtering will be applied.
  public $done = TRUE;

  // The log type to filter by. If this is NULL, no filtering will be applied.
  public $type = NULL;

  // Whether or not to limit the query to a single result.
  public $single = TRUE;

  /**
   * Construct.
   */
  public function __construct() {

    // Build a query of the log table with our prefix.
    $this->query = db_select('log',  $this->prefix . 'log');

    // Add a query tag to identify where this came from.
    $this->query->addTag($this->tag);
  }

  /**
   * Build a select query of logs.
   *
   * This method is used by other modules to build queries and Views handlers
   * that need to find the most recent log in a specific context.
   *
   * Extending classes can use this to generate a base query, and then add their
   * own modifications on top of that.
   */
  public function build() {

    /**
     * This query may be used as a sub-query join in a Views handler via the
     * views_join_subquery class (for an example see:
     * farm_movement_handler_relationship_location). When a sub-query is added
     * via views_join_subquery, it is not possible to use query arguments in the
     * sub-query itself. So we cannot use the query::condition() method, or any
     * other methods that take query arguments separately and perform sanitation
     * on them. Thus, it is the responsibility of this function to sanitize any
     * inputs and use them directly in the SQL.
     */

    // Ensure $time is valid, because it may be used directly in the query
    // string. This is defensive code. See note about views_join_subquery above.
    if (!is_numeric($this->time) || $this->time < 0) {
      $this->time = REQUEST_TIME;
    }

    // Ensure that $type is a valid strings, because we use it directly in the
    // query's WHERE statements below. This is defensive code. See note about
    // views_join_subquery in farm_log_query().
    if (!is_null($this->type)) {
      $this->type = db_escape_field($this->type);
    }

    // If $type is not empty, filter to logs of that type.
    if (!empty($this->type)) {
      $this->query->where($this->prefix . "log.type = '" . $this->type . "'");
    }

    // If $time is not zero, limit to only logs before it. This allows the
    // absolute last log to be found by setting $time to zero.
    if ($this->time !== 0) {
      $this->query->where($this->prefix . 'log.timestamp <= ' . $this->time);
    }

    // Filter logs based on whether they are done or not. This will only happen
    // if $done is explicitly set to TRUE or FALSE. If any other value is used,
    // filtering will not take place.
    if ($this->done === TRUE) {
      $this->query->where($this->prefix . 'log.done = 1');
    }
    elseif ($this->done === FALSE) {
      $this->query->where($this->prefix . 'ss_log.done = 0');
    }

    // Order by timestamp and then log id, descending.
    $this->query->orderBy($this->prefix . 'log.timestamp', 'DESC');
    $this->query->orderBy($this->prefix . 'log.id', 'DESC');

    // Limit the query to a single result (the first one), if desired.
    if (!empty($this->single)) {
      $this->query->range(0, 1);
    }

    // Return the query object.
    return $this->query;
  }
}
