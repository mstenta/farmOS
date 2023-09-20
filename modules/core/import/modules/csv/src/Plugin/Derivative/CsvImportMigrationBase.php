<?php

namespace Drupal\farm_import_csv\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for CSV import migration derivatives.
 */
abstract class CsvImportMigrationBase extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * Set this in child classes.
   *
   * @var string
   */
  protected string $entityType;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CsvImportMigration constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
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
    // Do nothing.
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

      // If the entity type has a bundle_plugin manager, add column mappings
      // and descriptions for bundle fields.
      if ($this->entityTypeManager->hasHandler($this->entityType, 'bundle_plugin')) {
        $bundle_fields = $this->entityTypeManager->getHandler($this->entityType, 'bundle_plugin')->getFieldDefinitions($bundle->id());
        foreach ($bundle_fields as $field_definition) {
          $this->addBundleField($field_definition, $definition['process'], $definition['third_party_settings']['farm_import_csv']['columns']);
        }
      }

      // Alter migration process mapping for this bundle.
      $this->alterProcessMapping($definition['process'], $bundle->id());

      // Alter column descriptions for this bundle.
      $this->alterColumnDescriptions($definition['third_party_settings']['farm_import_csv']['columns'], $bundle->id());

      // Add access control permissions to third party settings.
      $definition['third_party_settings']['farm_import_csv']['access']['permissions'][] = $this->getCreatePermission($bundle->id());

      $definitions[$bundle->id()] = $definition;
    }

    // Return migration definitions.
    return $definitions;
  }

  /**
   * Adds bundle field mapping configuration for supported field types.
   *
   * @param \Drupal\entity\BundleFieldDefinition $field_definition
   *   The field definition.
   * @param array &$mapping
   *    The migration process mapping.
   * @param array &$columns
   *    The column descriptions from third-party settings.
   */
  protected function addBundleField($field_definition, &$mapping, &$columns): void {

    // Get the field name.
    $field_name = $field_definition->getName();

    // Generate column name (replace underscores with spaces).
    $column_name = str_replace('_', ' ', $field_name);

    // Add configuration based on field type.
    switch ($field_definition->getType()) {

      // Boolean field.
      case 'boolean':
        # @todo
        # Accept 1, 0, y, n, yes, no, true, false (convert to lowercase)

        break;

      // Entity reference field.
      case 'entity_reference':
        # @todo
        # Support assets and terms.
        break;

      // List of strings field.
      case 'list_string':
        if (!empty($field_definition->getSetting('allowed_values'))) {
          $allowed_values = $field_definition->getSetting('allowed_values');
        }
        elseif (!empty($field_definition->getSetting('allowed_values_function'))) {
          $allowed_values = call_user_func($field_definition->getSetting('allowed_values_function'), $field_definition);
        }
        else {
          return;
        }

        // Generate an "allowed values" message.
        $allowed_values_message = t('Allowed values');
        $allowed_values_message .= ': ' . implode(', ', array_keys($allowed_values));

        // Filter out values that are not allowed.
        $values_map = array_combine(array_keys($allowed_values), array_keys($allowed_values));
        $mapping[$field_name][] = [
          'plugin' => 'static_map',
          'source' => $column_name,
          'map' => $values_map,
          'default_value' => '',
        ];

        // If no value was mapped, skip the row.
        $mapping[$field_name][] = [
          'plugin' => 'skip_on_empty',
          'method' => 'row',
          'message' => $allowed_values_message,
        ];

        // Add allowed values message to the extra description.
        $extra_description = $allowed_values_message;
        break;

      // String field.
      case 'string':

        // Map directly from source.
        $mapping[$field_name] = [
          'plugin' => 'get',
          'source' => $column_name,
        ];
        break;

      // Timestamp.
      case 'timestamp':

        # Parse with strtotime().
        $mapping[$field_name] = [
          'plugin' => 'callback',
          'callable' => 'strtotime',
          'source' => $column_name,
        ];

        # Describe allowed values.
        $extra_description = $this->t('Accepts most date/time formats.');
        break;
    }

    // Add column description.
    $description = (string) $field_definition->getDescription();
    if (!empty($extra_description)) {
      $description .= $extra_description;
    }
    $columns[] = [
      'name' => $column_name,
      'description' => $description,
    ];
  }

}
