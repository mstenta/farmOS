<?php

namespace Drupal\farm_sensor\Plugin\Asset\AssetType;

use Drupal\asset\Plugin\Asset\AssetType\AssetTypeBase;

/**
 * Provides the manual payment type.
 *
 * @AssetType(
 *   id = "sensor",
 *   label = @Translation("Sensor"),
 * )
 */
class Sensor extends AssetTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    return [];
  }

}
