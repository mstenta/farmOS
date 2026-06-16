<?php

declare(strict_types=1);

namespace Drupal\farm_import_csv\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\FieldConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for CSV import migration derivatives.
 *
 * @internal
 */
abstract class CsvImportMigrationBase extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * Set this in child classes.
   *
   * @var string
   */
  protected string $entityType;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFieldManagerInterface $entityFieldManager,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    // @todo Remove when DeriverBase provides a create() method with autowiring.
    // @see https://www.drupal.org/project/drupal/issues/3565338
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('module_handler'),
    );
  }

  /**
   * Get the entity create permission string for a given bundle.
   *
   * @param string $bundle
   *   The entity bundle.
   *
   * @return string
   *   Returns a permission string for creating entities of this bundle.
   */
  abstract protected function getCreatePermission(string $bundle): string;

  /**
   * Alter migration process mapping for a given bundle.
   *
   * @param array &$mapping
   *   The migration process mapping.
   * @param string $bundle
   *   The entity bundle.
   */
  protected function alterProcessMapping(array &$mapping, string $bundle): void {

    // If this is a log or asset, add a revision log message.
    if (in_array($this->entityType, ['asset', 'log'])) {
      $mapping['revision_log_message'] = [
        'plugin' => 'default_value',
        'default_value' => 'Imported via CSV.',
      ];
    }
  }

  /**
   * Alter column descriptions for a given bundle.
   *
   * @param array &$columns
   *   The column descriptions from third-party settings.
   * @param string $bundle
   *   The entity bundle.
   */
  protected function alterColumnDescriptions(array &$columns, string $bundle): void {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $definitions = [];

    // If the entity type is not defined, return nothing.
    if (empty($this->entityType)) {
      return $definitions;
    }

    // Load all bundles for this entity type.
    $entity_type = $this->entityTypeManager->getDefinition($this->entityType);
    $bundles = $this->entityTypeManager->getStorage($entity_type->getBundleEntityType())->loadMultiple();

    // Generate a migration for each bundle.
    foreach ($bundles as $bundle) {
      $definition = $base_plugin_definition;

      // Set the migration ID and label.
      $definition['id'] .= ':' . $bundle->id();
      $definition['label'] = $entity_type->getLabel() . ': ' . $bundle->label();

      // Alter migration process mapping for this bundle.
      $this->alterProcessMapping($definition['process'], $bundle->id());

      // Alter column descriptions for this bundle.
      $this->alterColumnDescriptions($definition['third_party_settings']['farm_import_csv']['columns'], $bundle->id());

      // Ask modules for a list of fields to exclude.
      $exclude_fields = $this->moduleHandler->invokeAll('farm_import_csv_exclude_fields', [$this->entityType]);

      // Add column mapping and descriptions for base fields provided by other
      // modules.
      $base_fields = $this->moduleHandler->invokeAll('farm_import_csv_base_fields', [$this->entityType]);
      foreach ($this->entityFieldManager->getBaseFieldDefinitions($this->entityType) as $field_definition) {
        /** @var \Drupal\Core\Field\BaseFieldDefinition $field_definition */
        if (!in_array($field_definition->getName(), $base_fields)) {
          continue;
        }
        if (in_array($field_definition->getName(), $exclude_fields)) {
          continue;
        }
        $this->addFieldMapping($field_definition, $definition['process'], $definition['third_party_settings']['farm_import_csv']['columns']);
      }

      // If the entity type has a bundle_plugin manager, add column mappings
      // and descriptions for bundle fields.
      if ($this->entityTypeManager->hasHandler($this->entityType, 'bundle_plugin')) {
        $bundle_fields = $this->entityTypeManager->getHandler($this->entityType, 'bundle_plugin')->getFieldDefinitions($bundle->id());
        foreach ($bundle_fields as $field_definition) {
          if (in_array($field_definition->getName(), $exclude_fields)) {
            continue;
          }
          $this->addFieldMapping($field_definition, $definition['process'], $definition['third_party_settings']['farm_import_csv']['columns']);
        }
      }

      // If the bundle has fields defined by configuration, add column mappings
      // and descriptions for them.
      // @todo descriptions?
      // @todo alter so we can say "days to maturity" and "days to transplant" instead of "maturity days" ...
      $config_field_definitions = array_filter($this->entityFieldManager->getFieldDefinitions($this->entityType, $bundle->id()), function ($definition) {
        return $definition instanceof FieldConfigInterface;
      });
      foreach ($config_field_definitions as $field_definition) {
        if (in_array($field_definition->getName(), $exclude_fields)) {
          continue;
        }
        $this->addFieldMapping($field_definition, $definition['process'], $definition['third_party_settings']['farm_import_csv']['columns']);
      }

      // Add access control permissions to third party settings.
      $definition['third_party_settings']['farm_import_csv']['access']['permissions'][] = $this->getCreatePermission($bundle->id());

      $definitions[$bundle->id()] = $definition;
    }

    // Return migration definitions.
    return $definitions;
  }

  /**
   * Adds field mapping configuration for supported field types.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array &$mapping
   *   The migration process mapping.
   * @param array &$columns
   *   The column descriptions from third-party settings.
   */
  protected function addFieldMapping(FieldDefinitionInterface $field_definition, array &$mapping, array &$columns): void {

    // This only supports certain field types.
    $supported_field_types = [
      'boolean',
      'decimal',
      'entity_reference',
      'integer',
      'list_string',
      'string',
      'timestamp',
    ];
    if (!in_array($field_definition->getType(), $supported_field_types)) {
      return;
    }

    // Get the field name.
    $field_name = $field_definition->getName();

    // Generate column name (replace underscores with spaces).
    $column_name = str_replace('_', ' ', $field_name);

    // Start a process pipeline and column descriptions array.
    $process = [];
    $description = [(string) $field_definition->getDescription()];

    // Add configuration based on field type.
    switch ($field_definition->getType()) {

      // Boolean.
      case 'boolean':

        // Parse with the boolean plugin.
        $process[] = [
          'plugin' => 'boolean',
        ];

        // Describe allowed values.
        $description[] = $this->t('Accepts most boolean values.');
        break;

      // Number fields.
      case 'integer':
      case 'decimal':

        // Map directly from source.
        $process[] = [
          'plugin' => 'get',
        ];
        break;

      // Entity reference field.
      case 'entity_reference':

        // Asset reference.
        if ($field_definition->getSetting('target_type') == 'asset') {
          $plugin = [
            'plugin' => 'asset_lookup',
          ];
          if (!empty($field_definition->getSetting('handler_settings')['target_bundles'])) {
            $plugin['bundle'] = array_keys($field_definition->getSetting('handler_settings')['target_bundles']);
          }
          $process[] = $plugin;
          $description[] = $this->t('Accepts asset names, ID tags, UUIDs, and IDs.');
        }

        // Organization reference.
        if ($field_definition->getSetting('target_type') == 'organization') {
          $plugin = [
            'plugin' => 'organization_lookup',
          ];
          if (!empty($field_definition->getSetting('handler_settings')['target_bundles'])) {
            $plugin['bundle'] = array_keys($field_definition->getSetting('handler_settings')['target_bundles']);
          }
          $process[] = $plugin;
          $description[] = $this->t('Accepts organization names, UUIDs, and IDs.');
        }

        // Term reference.
        elseif ($field_definition->getSetting('target_type') == 'taxonomy_term') {
          $process[] = [
            'plugin' => 'term_lookup',
            'bundle' => $field_definition->getSetting('handler_settings')['target_bundles'],
          ];
        }

        break;

      // String fields.
      case 'string':
      case 'list_string':

        // Map directly from source.
        $process[] = [
          'plugin' => 'get',
        ];

        // Add a list of allowed values to the column description.
        if (!empty($field_definition->getSetting('allowed_values'))) {
          $allowed_values = $field_definition->getSetting('allowed_values');
        }
        elseif (!empty($field_definition->getSetting('allowed_values_function'))) {
          $allowed_values = call_user_func($field_definition->getSetting('allowed_values_function'), $field_definition);
        }
        if (!empty($allowed_values)) {
          $allowed_values_description = $this->t('Allowed values');
          $allowed_values_description .= ': ' . implode(', ', array_keys($allowed_values)) . '.';
          $description[] = $allowed_values_description;
        }

        break;

      // Timestamp.
      case 'timestamp':

        // If this is not required, then skip the process if empty.
        if (!$field_definition->isRequired()) {
          $process[] = [
            'plugin' => 'skip_on_empty',
            'method' => 'process',
          ];
        }

        // Parse with strtotime().
        $process[] = [
          'plugin' => 'callback',
          'callable' => 'strtotime',
        ];

        // Describe allowed values.
        $description[] = $this->t('Accepts most date/time formats.');
        break;
    }

    // If the field supports multiple values, explode on comma delimiter
    // as a first step and describe how to format values.
    if ($field_definition->getFieldStorageDefinition()->getCardinality() === -1 || $field_definition->getFieldStorageDefinition()->getCardinality() > 1) {
      array_unshift($process, ['plugin' => 'explode', 'delimiter' => ',']);
      array_unshift($process, ['plugin' => 'skip_on_empty', 'method' => 'process']);
      $description[] = $this->t('Multiple values can be separated by commas with the whole cell wrapped in quotes.');
    }

    // If the field is required, make note of that in the column description.
    if ($field_definition->isRequired()) {
      $description[] = $this->t('Required.');
    }

    // If a process pipeline has been defined, add the source to the first
    // plugin, add the pipeline to the mapping, and add the column description.
    if (!empty($process)) {
      $process[0]['source'] = $column_name;
      $mapping[$field_name] = $process;
      $columns[] = [
        'name' => $column_name,
        'description' => implode(' ', $description),
      ];
    }
  }

}
