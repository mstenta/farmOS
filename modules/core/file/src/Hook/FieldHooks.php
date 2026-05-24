<?php

declare(strict_types=1);

namespace Drupal\farm_file\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\farm_field\FarmFieldFactoryInterface;

/**
 * Field hook implementations for farm_file.
 */
class FieldHooks {

  use StringTranslationTrait;

  public function __construct(
    protected FarmFieldFactoryInterface $farmFieldFactory,
  ) {}

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {

    // Add file and image fields to entity types.
    if (!in_array($entity_type->id(), [
      'asset',
      'log',
      'organization',
      'plan',
      'taxonomy_term',
    ])) {
      return [];
    }
    $field_info = [
      'file' => [
        'type' => 'file',
        'label' => $this->t('Files'),
        'file_directory' => 'farm/' . $entity_type->id() . '/[date:custom:Y]-[date:custom:m]',
        'multiple' => TRUE,
        'weight' => [
          'form' => 90,
          'view' => 90,
        ],
      ],
      'image' => [
        'type' => 'image',
        'label' => $this->t('Images'),
        'file_directory' => 'farm/' . $entity_type->id() . '/[date:custom:Y]-[date:custom:m]',
        'multiple' => TRUE,
        'weight' => [
          'form' => 89,
          'view' => 89,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

}
