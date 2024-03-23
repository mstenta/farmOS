<?php

namespace Drupal\farm_report_geometry\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for running geometry reports.
 */
class GeometryReportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_report_geometry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Require PostgreSQL with the PostGIS extension.
    if (!(\Drupal::database()->getConnectionOptions()['driver'] == 'pgsql' && \Drupal::database()->query("SELECT COUNT(*) FROM pg_extension WHERE extname = 'postgis'")->fetchField())) {
      $this->messenger()->addError('The geometry report only supports PostgreSQL databases with the PostGIS extension.');
      return $form;
    }

    // Geometry input map.
    $form['geometry'] = [
      '#type' => 'farm_map_input',
      '#title' => t('Geometry'),
      '#title_display' => 'hidden',
      '#map_type' => 'default',
      '#required' => TRUE,
    ];

    // Search button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Query for log IDs.
    // We need to use ST_GeomFromText(convert_from(geometry_value, 'UTF8'))
    // instead of ST_GeomFromWKB(geometry_value)) because ...
    $sql = "SELECT entity_id FROM {log__geometry} WHERE ST_Intersects(ST_GeomFromText(convert_from(geometry_value, 'UTF8'), :srid), ST_GeomFromText(:wkt, :srid))";
    $args = [
      ':wkt' => $form_state->getValue('geometry'),
      ':srid' => 4326,
    ];
    $log_ids = \Drupal::database()->query($sql, $args)->fetchCol();

    $d=1;
  }

}
