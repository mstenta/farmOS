<?php

namespace Drupal\farm_compost\Plugin\Asset\AssetType;

use Drupal\asset\Plugin\Asset\AssetType\AssetTypeBase;

/**
 * Provides the manual payment type.
 *
 * @AssetType(
 *   id = "compost",
 *   label = @Translation("Compost"),
 * )
 */
class Compost extends AssetTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    return [];
  }

}
