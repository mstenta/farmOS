<?php

namespace Drupal\farm_log;

use Drupal\asset\Entity\AssetInterface;

/**
 * The interface for asset logs service.
 */
interface AssetLogsInterface {

  /**
   * Get all logs for an asset.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   * @param string|null $log_type
   *   Optionally filter by log type.
   * @param bool $access_check
   *   Whether to check log entity access (defaults to TRUE).
   *
   * @return \Drupal\log\Entity\LogInterface[]
   *   Returns an array of Log entities.
   */
  public function getLogs(AssetInterface $asset, ?string $log_type = NULL, bool $access_check = TRUE): array;

  /**
   * Get the first log of an asset.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   * @param string|null $log_type
   *   Optionally filter by log type.
   * @param bool $access_check
   *   Whether to check log entity access.
   *
   * @return \Drupal\log\Entity\LogInterface|null
   *   Returns a log entity or NULL if no logs were found.
   */
  public function getFirstLog(AssetInterface $asset, ?string $log_type = NULL, bool $access_check = TRUE);

}
