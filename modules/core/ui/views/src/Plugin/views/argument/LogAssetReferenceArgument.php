<?php

namespace Drupal\farm_ui_views\Plugin\views\argument;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\views\Views;

/**
 * Argument handler for filtering logs by multiple asset reference fields.
 *
 * @ViewsArgument("log_asset_reference")
 */
class LogAssetReferenceArgument extends ArgumentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isExposed() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    $form['asset_reference_fields'] = [
      '#type' => 'checkboxes',
      '#title' => 'Asset references',
      '#options' => $this->assetReferenceFields(),
      '#default_value' => array_keys($this->assetReferenceFields()),
      '#weight' => 100,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {

    // Join all the asset reference fields that were selected in the exposed
    // form.
    // @todo Get this from the submitted form.
    $asset_reference_fields = array_keys($this->assetReferenceFields());

    // Join each of the asset reference field tables with a condition to match
    // the asset ID argument.
    $this->ensureMyTable();
    $aliases = [];
    foreach ($asset_reference_fields as $field_name) {
      /** @var \Drupal\views\Plugin\views\join\JoinPluginBase $join */
      $join = Views::pluginManager('join')->createInstance('standard', [
        'table' => 'log__' . $field_name,
        'field' => 'entity_id',
        'left_table' => $this->table,
        'left_field' => 'id',
        'extra' => [
          [
            'field' => 'deleted',
            'value' => 0,
          ],
          [
            'field' => $field_name . '_target_id',
            'value' => $this->argument,
          ],
        ],
      ]);
      $aliases[$field_name] = $this->query->addRelationship('log__' . $field_name, $join, $this->table);
    }

    // Limit the query to only include logs that reference the asset on ONE of
    // the asset reference fields. This must be added in a single where
    // expression so the condition is not combined with other filters from the
    // view.
    $conditions = [];
    foreach ($asset_reference_fields as $field_name) {
      $conditions[] = $aliases[$field_name] . '.' . $field_name . '_target_id IS NOT NULL';
    }
    $this->query->addWhereExpression(0, $this->table . '.id IS NOT NULL AND (' . implode(' OR ', $conditions). ')');
  }

  /**
   * Define available asset reference fields.
   */
  protected function assetReferenceFields() {
    return [
      'asset' => $this->t('Assets'),
      'location' => $this->t('Location'),
      'equipment' => $this->t('Equipment'),
      'group' => $this->t('Group'),
    ];
  }

}
