<?php

namespace Drupal\farm_quantity_time\Plugin\Quantity\QuantityType;

use Drupal\farm_entity\Plugin\Quantity\QuantityType\FarmQuantityType;

/**
 * Provides the time quantity type.
 *
 * @QuantityType(
 *   id = "time",
 *   label = @Translation("Time"),
 * )
 */
class Time extends FarmQuantityType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {

    // Inherit default quantity fields.
    $fields = parent::buildFieldDefinitions();

    // Add time specific quantity fields.
    $field_info = [
      'user' => [
        'type' => 'entity_reference',
        'label' => $this->t('User'),
        'description' => $this->t('Users associated with this time quantity.'),
        'target_type' => 'user',
        'multiple' => TRUE,
      ],
      'start' => [
        'type' => 'timestamp',
        'label' => $this->t('Start'),
        'weight' => [
          'view' => 3,
          'form' => 3,
        ],
      ],
      'end' => [
        'type' => 'timestamp',
        'label' => $this->t('End'),
        'weight' => [
          'view' => 3,
          'form' => 3,
        ],
      ],
    ];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->bundleFieldDefinition($info);
    }
    return $fields;
  }

}
