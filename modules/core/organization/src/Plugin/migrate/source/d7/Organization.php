<?php

namespace Drupal\organization\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Organization source from database.
 *
 * @MigrateSource(
 *   id = "d7_organization",
 *   source_module = "farm_organization"
 * )
 *
 * @deprecated in farm:3.0.0 and is removed from farm:4.0.0. Support for farmOS
 *   v1 migrations was dropped in farmOS 3.x.
 * @see https://www.drupal.org/project/farm/issues/3410701
 * @see https://www.drupal.org/project/farm/issues/3382616
 */
class Organization extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('farm_organization', 'fa')
      ->fields('fa')
      ->distinct()
      ->orderBy('id');

    if (isset($this->configuration['bundle'])) {
      $query->condition('fa.type', (array) $this->configuration['bundle'], 'IN');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('The organization ID'),
      'name' => $this->t('The organization name'),
      'type' => $this->t('The organization type'),
      'uid' => $this->t('The organization author ID'),
      'created' => $this->t('Timestamp when the organization was created'),
      'changed' => $this->t('Timestamp when the organization was last modified'),
      'archived' => $this->t('Timestamp when the organization was archived'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $id = $row->getSourceProperty('id');
    $type = $row->getSourceProperty('type');

    // Get Field API field values.
    foreach ($this->getFields('farm_organization', $type) as $field_name => $field) {
      $row->setSourceProperty($field_name, $this->getFieldValues('farm_organization', $field_name, $id));
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['id']['type'] = 'integer';
    return $ids;
  }

}
