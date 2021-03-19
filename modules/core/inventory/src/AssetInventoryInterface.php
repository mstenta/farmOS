<?php

namespace Drupal\farm_inventory;

use Drupal\asset\Entity\AssetInterface;

/**
 * Asset inventory logic.
 */
interface AssetInventoryInterface {

  /**
   * Check if an asset has inventory.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The Asset entity.
   *
   * @return bool
   *   Returns TRUE if the asset has inventory, FALSE otherwise.
   */
  public function hasInventory(AssetInterface $asset): bool;

  /**
   * Get inventory summaries for an asset.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The Asset entity.
   *
   * @return array
   *   Returns an array of asset inventory information.
   */
  public function getInventory(AssetInterface $asset): array;

}
