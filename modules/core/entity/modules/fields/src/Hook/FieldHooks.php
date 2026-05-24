<?php

declare(strict_types=1);

namespace Drupal\farm_entity_fields\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\farm_field\FarmFieldFactoryInterface;

/**
 * Field hook implementations for farm_entity_fields.
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

    // Add common base fields to entity types.
    if ($entity_type->id() == 'asset') {
      return $this->assetFields();
    }
    elseif ($entity_type->id() == 'log') {
      return $this->logFields();
    }
    elseif ($entity_type->id() == 'organization') {
      return $this->organizationFields();
    }
    elseif ($entity_type->id() == 'plan') {
      return $this->planFields();
    }
    elseif ($entity_type->id() == 'taxonomy_term') {
      return $this->termFields();
    }
    return [];
  }

  /**
   * Implements hook_entity_base_field_info_alter().
   */
  #[Hook('entity_base_field_info_alter')]
  public function entityBaseFieldInfoAlter(&$fields, EntityTypeInterface $entity_type) {

    // Only alter asset, log, organization, and plan fields.
    if (!in_array($entity_type->id(), [
      'asset',
      'log',
      'organization',
      'plan',
    ])) {
      return;
    }
    $alter_fields = [
      'name' => [
        'label' => 'hidden',
        'weight' => -100,
      ],
      'status' => [
        'weight' => -95,
      ],
      'timestamp' => [
        'weight' => -90,
      ],
      'type' => [
        'weight' => -85,
        'hidden' => 'form',
      ],
      'created' => [
        'hidden' => TRUE,
      ],
      'uid' => [
        'hidden' => TRUE,
      ],
    ];
    foreach ($alter_fields as $name => $options) {

      // If the field does not exist on this entity type, skip it.
      if (empty($fields[$name])) {
        continue;
      }

      // Load the form and view display options.
      $form_display_options = $fields[$name]->getDisplayOptions('form');
      $view_display_options = $fields[$name]->getDisplayOptions('view');

      // Set the field weight.
      if (!empty($options['weight'])) {
        $form_display_options['weight'] = $view_display_options['weight'] = $options['weight'];
      }

      // Hide the field, if desired.
      if (!empty($options['hidden'])) {
        /** @var bool|string $hidden */
        $hidden = $options['hidden'];
        if ($hidden === TRUE || $hidden === 'form') {
          $form_display_options['region'] = 'hidden';
        }
        if ($hidden === TRUE || $hidden === 'view') {
          $view_display_options['region'] = 'hidden';
        }
      }

      // Set the label to inline by default, but allow overrides.
      $view_display_options['label'] = 'inline';
      if (!empty($options['label'])) {
        $view_display_options['label'] = $options['label'];
      }
      switch ($name) {

        // Change state field from transition form to default.
        case 'status':
          $view_display_options['type'] = 'list_default';
          break;

        // Don't display a link to the entity type reference.
        case 'type':
          $view_display_options['settings']['link'] = FALSE;
          break;
      }

      // Save the options.
      $fields[$name]->setDisplayOptions('form', $form_display_options);
      $fields[$name]->setDisplayOptions('view', $view_display_options);
    }

    // Allow the "type" base field view display to be configured.
    if (!empty($fields['type'])) {
      $fields['type']->setDisplayConfigurable('view', TRUE);
    }
  }

  /**
   * Define common asset base fields.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   Returns an array of base field definitions.
   */
  private function assetFields(): array {
    $field_info = [
      'data' => [
        'type' => 'string_long',
        'label' => $this->t('Data'),
        'hidden' => TRUE,
      ],
      'notes' => [
        'type' => 'text_long',
        'label' => $this->t('Notes'),
        'weight' => [
          'form' => 95,
          'view' => 95,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

  /**
   * Define common log base fields.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   Returns an array of base field definitions.
   */
  private function logFields(): array {
    $field_info = [
      'data' => [
        'type' => 'string_long',
        'label' => $this->t('Data'),
        'hidden' => TRUE,
      ],
      'notes' => [
        'type' => 'text_long',
        'label' => $this->t('Notes'),
        'weight' => [
          'form' => 95,
          'view' => 95,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

  /**
   * Define common organization base fields.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   Returns an array of base field definitions.
   */
  private function organizationFields(): array {
    $field_info = [
      'data' => [
        'type' => 'string_long',
        'label' => $this->t('Data'),
        'hidden' => TRUE,
      ],
      'notes' => [
        'type' => 'text_long',
        'label' => $this->t('Notes'),
        'weight' => [
          'form' => 95,
          'view' => 10,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

  /**
   * Define common plan base fields.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   Returns an array of base field definitions.
   */
  private function planFields(): array {
    $field_info = [
      'data' => [
        'type' => 'string_long',
        'label' => $this->t('Data'),
        'hidden' => TRUE,
      ],
      'notes' => [
        'type' => 'text_long',
        'label' => $this->t('Notes'),
        'weight' => [
          'form' => 95,
          'view' => 95,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

  /**
   * Define common taxonomy term base fields.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   Returns an array of base field definitions.
   */
  private function termFields(): array {
    $field_info = [
      'external_uri' => [
        'type' => 'uri',
        'label' => $this->t('External URI'),
        'description' => $this->t('Link this term to one or more external URLs or ontology item URIs.'),
        'multiple' => TRUE,
        'weight' => [
          'form' => 80,
          'view' => 80,
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
