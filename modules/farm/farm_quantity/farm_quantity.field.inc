<?php
/**
 * @file
 * Farm quantity field type definition.
 */

/**
 * Implements of hook_field_info().
 */
function farm_quantity_field_info() {
  return array(
    'quantity' => array(
      'label' => t('Quantity'),
      'description' => t('This field stores quantity measurements, including measure, value, units, and label.'),
      'default_widget' => 'quantity_default',
      'default_formatter' => 'quantity_default',
    ),
  );
}

/**
 * Implements hook_field_validate().
 */
function farm_quantity_field_validate($entity_type, $entity, $field, $instance, $langcode, $items, &$errors) {

}

/**
 * Implements hook_field_is_empty().
 */
function farm_quantity_field_is_empty($item, $field) {
  return empty($item['measure']) && (empty($item['value_numerator']) || empty($item['value_denominator'])) && empty($item['units']) && empty($item['label']);
}

/**
 * Implements of hook_field_widget_info().
 */
function farm_quantity_field_widget_info() {
  return array(
    'quantity_default' => array(
      'label' => t('Quantity measurement'),
      'field types' => array('quantity'),
    ),
  );
}

/**
 * Implements hook_field_widget_form().
 */
function farm_quantity_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {

  // Quantity measure.
  $measure = isset($items[$delta]['measure']) ? $items[$delta]['measure'] : '';
  $element['measure'] = array(
    '#type' => 'select',
    '#title' => t('Measure'),
    '#description' => t('What type of measurement is this quantity?'),
    '#options' => array('' => '') + farm_quantity_measure_options(),
    '#default_value' => $measure,
  );

  // Quantity value.
  // This will be converted to a fraction numerator and denominator in
  // farm_quantity_element_validate().
  $value = '';
  if (isset($items[$delta]['value_numerator']) && isset($items[$delta]['value_denominator'])) {
    $value = fraction($items[$delta]['value_numerator'], $items[$delta]['value_denominator'])->toDecimal(0, TRUE);
  }
  $element['value'] = array(
    '#type' => 'textfield',
    '#title' => t('Value'),
    '#description' => t(''),
    '#default_value' => $value,
    '#element_validate' => array('element_validate_number'),
  );

  // Quantity unit name.
  // This will be used to get a taxonomy term ID in
  // farm_quantity_element_validate().
  $unit = '';
  if (isset($items[$delta]['unit'])) {
    $term = taxonomy_term_load($items[$delta]['unit']);
    if (!empty($term->name)) {
      $unit = $term->name;
    }
  }
  $element['unit_label'] = array(
    '#type' => 'textfield',
    '#title' => t('Unit'),
    '#description' => t(''),
    '#default_value' => $unit,
    /**
     * @todo
     * Reimplement autocomplete path callback internally, instead of relying
     * on the path provided by the Taxonomy module, which assumes that a term
     * reference field exists. If we remove the field_farm_quantity_units field
     * then that path will no longer work.
     */
    '#autocomplete_path' => 'taxonomy/autocomplete/field_farm_quantity_units',
  );

  // Quantity label.
  $label = isset($items[$delta]['label']) ? $items[$delta]['label'] : NULL;
  $element['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('Optionally give this quantity measurement a label. This helps to differentiate multiple quantities with the same unit and measure that are in the same log.'),
    '#default_value' => $label,
    '#maxlength' => 255,
  );

  // Add element validation.
  $element['#element_validate'] = array('farm_quantity_element_validate');

  // Return the form element.
  return $element;
}

/**
 * Quantity field element validation.
 */
function farm_quantity_element_validate($element, &$form_state) {

  // Search through the form values to find the current field value.
  $parents = $element['#parents'];
  $values = drupal_array_get_nested_value($form_state['values'], $parents);

  // If the measure is empty, set it to NULL.
  // This ensures that empty text fields are null in the database.
  if ($values['measure'] == '') {
    $values['measure'] = NULL;
  }

  // If the value is not empty, build the numerator and denominator.
  // Without a value, these will be null in the database.
  if ($values['value'] != '') {
    $fraction = fraction_from_decimal($values['value']);
    $values['value_numerator'] = $fraction->getNumerator();
    $values['value_denominator'] = $fraction->getDenominator();
  }

  // If the unit is not empty, get the term ID.
  // Without a value, this will be null in the database.
  if (!empty($values['unit_label'])) {
    $term = farm_term($values['unit_label'], 'farm_quantity_units');
    if (!empty($term->tid)) {
      $values['unit'] = $term->tid;
    }
  }

  // If the label is empty, set it to NULL.
  // This ensures that empty text fields are null in the database.
  if ($values['label'] == '') {
    $values['label'] = NULL;
  }

  // Set the element's form values.
  form_set_value($element, $values, $form_state);
}

/**
 * Implements hook_field_widget_error().
 */
function farm_quantity_field_widget_error($element, $error, $form, &$form_state) {

}

/**
 * Implements of hook_field_info().
 */
function farm_quantity_field_formatter_info() {
  return array(
    'quantity_default' => array(
      'label' => t('Quantity measurement'),
      'field types' => array('quantity'),
    ),
  );
}

/**
 * Implements hook_field_formatter_view().
 */
function farm_quantity_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {

}
