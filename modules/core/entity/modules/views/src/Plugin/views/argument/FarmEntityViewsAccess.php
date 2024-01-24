<?php

namespace Drupal\farm_entity_views\Plugin\views\argument;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\DefaultTableMapping;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\entity\QueryAccess\ConditionGroup;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A handler for filtering entities based on access permissions.
 *
 * This uses Entity API module's query access conditions to filter out entities
 * that users do not have access to based on their permissions. Note that this
 * filter does not use $entity->access() so it is not a complete solution to
 * access checking. It serves as a basic pre-filter, though, which is useful in
 * a lot of cases.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("farm_entity_views_access")
 */
class FarmEntityViewsAccess extends ArgumentPluginBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs an AssetLocation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $connection
   *    The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *    The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {

    // If the query is not an instance of Sql, bail.
    // This mimics the condition in entity_views_query_alter() and appeases
    // recursive $this->mapConditions() call below.
    if (!$this->query instanceof Sql) {
      return;
    }

    // Load the user from the argument.
    $account = $this->entityTypeManager->getStorage('user')->load($this->argument);

    // This logic is inspired by and adapted from Entity API's ViewsQueryAlter.
    // The only difference is that we do not look for an access handler class
    // on the entity definition. We always use UncacheableQueryAccessHandler.
    // @see \Drupal\entity\QueryAccess\ViewsQueryAlter
    // @see \Drupal\entity\QueryAccess\UncacheableQueryAccessHandler
    $table_info = $this->query->getEntityTableInfo();
    $base_table = reset($table_info);
    if (empty($base_table['entity_type']) || $base_table['relationship_id'] != 'none') {
      return;
    }
    $entity_type_id = $base_table['entity_type'];
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    if (!$storage instanceof SqlContentEntityStorage) {
      return;
    }

    /** @var \Drupal\entity\QueryAccess\QueryAccessHandlerInterface $query_access */
    $query_access = $this->entityTypeManager->createHandlerInstance(UncacheableQueryAccessHandler::class, $entity_type);
    $conditions = $query_access->getConditions('view', $account);
    if ($conditions->isAlwaysFalse()) {
      $this->query->addWhereExpression(0, '1 = 0');
    }
    elseif (count($conditions)) {
      // Store the data table, in case mapConditions() needs to join it in.
      $base_table['data_table'] = $entity_type->getDataTable();
      $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type_id);
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
      $table_mapping = $storage->getTableMapping();
      $sql_conditions = $this->mapConditions($conditions, $this->query, $base_table, $field_storage_definitions, $table_mapping);
      $this->query->addWhere(0, $sql_conditions);
    }

  }

  /**
   * Maps an entity type's access conditions to views SQL conditions.
   *
   * This is copied directly from \Drupal\entity\QueryAccess\ViewsQueryAlter.
   * @see \Drupal\entity\QueryAccess\ViewsQueryAlter::mapConditions()
   *
   * @param \Drupal\entity\QueryAccess\ConditionGroup $conditions
   *   The access conditions.
   * @param \Drupal\views\Plugin\views\query\Sql $query
   *   The views query.
   * @param array $base_table
   *   The base table information.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface[] $field_storage_definitions
   *   The field storage definitions.
   * @param \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping
   *   The table mapping.
   *
   * @return \Drupal\Core\Database\Query\ConditionInterface
   *   The SQL conditions.
   */
  protected function mapConditions(ConditionGroup $conditions, Sql $query, array $base_table, array $field_storage_definitions, DefaultTableMapping $table_mapping) {
    $sql_condition = $this->connection->condition($conditions->getConjunction());
    foreach ($conditions->getConditions() as $condition) {
      if ($condition instanceof ConditionGroup) {
        $nested_sql_conditions = $this->mapConditions($condition, $query, $base_table, $field_storage_definitions, $table_mapping);
        $sql_condition->condition($nested_sql_conditions);
      }
      else {
        $field = $condition->getField();
        $property_name = NULL;
        if (strpos($field, '.') !== FALSE) {
          [$field, $property_name] = explode('.', $field);
        }
        // Skip unknown fields.
        if (!isset($field_storage_definitions[$field])) {
          continue;
        }
        $field_storage_definition = $field_storage_definitions[$field];
        if (!$property_name) {
          $property_name = $field_storage_definition->getMainPropertyName();
        }

        $column = $table_mapping->getFieldColumnName($field_storage_definition, $property_name);
        if ($table_mapping->requiresDedicatedTableStorage($field_storage_definitions[$field])) {
          if ($base_table['revision']) {
            $dedicated_table = $table_mapping->getDedicatedRevisionTableName($field_storage_definition);
          }
          else {
            $dedicated_table = $table_mapping->getDedicatedDataTableName($field_storage_definition);
          }
          // Views defaults to LEFT JOIN. For simplicity, we don't try to
          // use an INNER JOIN when it's safe to do so (AND conjunctions).
          $alias = $query->ensureTable($dedicated_table);
        }
        elseif ($base_table['revision'] && !$field_storage_definition->isRevisionable()) {
          // Workaround for #2652652, which causes $query->ensureTable()
          // to not work in this case, due to a missing relationship.
          if ($data_table = $query->getTableInfo($base_table['data_table'])) {
            $alias = $data_table['alias'];
          }
          else {
            $configuration = [
              'type' => 'INNER',
              'table' => $base_table['data_table'],
              'field' => 'id',
              'left_table' => $base_table['alias'],
              'left_field' => 'id',
            ];
            /** @var \Drupal\Views\Plugin\views\join\JoinPluginBase $join */
            $join = Views::pluginManager('join')->createInstance('standard', $configuration);
            $alias = $query->addRelationship($base_table['data_table'], $join, $data_table);
          }
        }
        else {
          $alias = $base_table['alias'];
        }

        $value = $condition->getValue();
        $operator = $condition->getOperator();
        // Using LIKE/NOT LIKE ensures a case insensitive comparison.
        // @see \Drupal\Core\Entity\Query\Sql\Condition::translateCondition().
        $property_definitions = $field_storage_definition->getPropertyDefinitions();
        $case_sensitive = $property_definitions[$property_name]->getSetting('case_sensitive');
        $operator_map = [
          '=' => 'LIKE',
          '<>' => 'NOT LIKE',
        ];
        if ($case_sensitive === FALSE && isset($operator_map[$operator])) {
          $operator = $operator_map[$operator];
          $value = $this->connection->escapeLike($value);
        }

        $sql_condition->condition("$alias.$column", $value, $operator);
      }
    }

    return $sql_condition;
  }

}
