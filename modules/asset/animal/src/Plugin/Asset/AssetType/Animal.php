<?php

namespace Drupal\farm_animal\Plugin\Asset\AssetType;

use Drupal\asset\Plugin\Asset\AssetType\AssetTypeBase;

/**
 * Provides the manual payment type.
 *
 * @AssetType(
 *   id = "animal",
 *   label = @Translation("Animal"),
 * )
 */
class Animal extends AssetTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];

    $options = [
      'type' => 'geofield',
      'label' => 'Geometry',
      'description' => 'Add geometry data to this log to describe where it took place.',
      'weight' => [
        'form' => 95,
        'view' => 95,
      ],
      'dedicated_table' => TRUE,
    ];
    $fields['geometry'] = farm_field_base_field_definition($options);

    return $fields;
  }

}
