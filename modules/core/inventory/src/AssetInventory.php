<?php

namespace Drupal\farm_inventory;

use Drupal\asset\Entity\AssetInterface;

/**
 * Asset inventory logic.
 */
class AssetInventory implements AssetInventoryInterface {

  /**
   * {@inheritdoc}
   */
  public function hasInventory(AssetInterface $asset): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInventory(AssetInterface $asset): array {
    return [];
  }

}
