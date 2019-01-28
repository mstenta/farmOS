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
      // Entity API integration.
      'property_type' => 'field_item_quantity',
      'property_callbacks' => array('quantity_property_info_callback'),
    ),
  );
}

/**
 * Callback to alter the property info of quantity fields.
 */
function quantity_property_info_callback(&$info, $entity_type, $field, $instance, $field_type) {
  $name = $field['field_name'];
  $property = &$info[$entity_type]['bundles'][$instance['bundle']]['properties'][$name];
  $property['type'] = ($field['cardinality'] != 1) ? 'list<field_item_quantity>' : 'field_item_quantity';
  $property['getter callback'] = 'entity_metadata_field_verbatim_get';
  $property['setter callback'] = 'entity_metadata_field_verbatim_set';
  $property['auto creation'] = 'farm_quantity_default_values';
  $property['property info'] = farm_quantity_data_property_info();
  unset($property['query callback']);
}

/**
 * Define default values for quantity fields.
 */
function farm_quantity_default_values() {
  return array(
    'measure' => NULL,
    'value_numerator' => NULL,
    'value_denominator' => NULL,
    'unit' => NULL,
    'label' => NULL,
  );
}

/**
 * Defines info for the properties of the quantity field data structure.
 */
function farm_quantity_data_property_info($name = NULL) {

  // Build an array of basic property information for the quantity field.
  $properties = array(
    'measure' => array(
      'label' => t('Measure'),
      'type' => 'text',
    ),
    'value' => array(
      'label' => t('Value'),
      'type' => 'decimal',
    ),
    'value_numerator' => array(
      'label' => t('Value Numerator'),
      'type' => 'integer',
    ),
    'value_denominator' => array(
      'label' => t('Value Denominator'),
      'type' => 'integer',
    ),
    'unit' => array(
      'label' => t('Unit'),
      'type' => 'integer',
    ),
    'unit_label' => array(
      'label' => t('Unit Label'),
      'type' => 'text',
    ),
    'label' => array(
      'label' => t('Label'),
      'type' => 'text',
    ),
  );
  foreach ($properties as &$property) {
    $property += array(
      'description' => !empty($name) ? t('!label of field %name', array('!label' => $property['label'], '%name' => $name)) : '',
      'getter callback' => 'entity_property_verbatim_get',
      'setter callback' => 'entity_property_verbatim_set',
    );
  }
  return $properties;
}

/**
 * Implements hook_field_load().
 */
function farm_quantity_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {

  // Iterate through entities and their items.
  foreach ($entities as $id => $entity) {
    foreach ($items[$id] as $delta => $item) {

      // Add a decimal representation of the quantity value.
      $value = NULL;
      if (!is_null($items[$id][$delta]['value_numerator']) && !is_null($items[$id][$delta]['value_denominator'])) {
        $value = fraction($items[$id][$delta]['value_numerator'], $items[$id][$delta]['value_denominator'])->toDecimal(0, TRUE);
      }
      $items[$id][$delta]['value'] = $value;

      // If a unit ID is available, load the taxonomy term name.
      $unit_label = NULL;
      if (!empty($items[$id][$delta]['unit'])) {
        $term = taxonomy_term_load($items[$id][$delta]['unit']);
        if (!empty($term->name)) {
          $unit_label = $term->name;
        }
      }
      $items[$id][$delta]['unit_label'] = $unit_label;
    }
  }
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

  // Define "empty" for each subfield.
  $empty_measure = empty($item['measure']);
  $empty_value = !isset($item['value_numerator']) || !isset($item['value_denominator']);
  $empty_unit = empty($item['unit']);
  $empty_label = empty($item['label']);

  // The field is empty if all the subfields are empty.
  return $empty_measure && $empty_value && $empty_unit && $empty_label;
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
  $element = array();

  // Load the list of measures.
  $measures = farm_quantity_measure_options();

  // Iterate through the items.
  foreach ($items as $delta => $item) {

    // If a measure is available, load the translated name.
    $measure = '';
    if (!empty($item['measure']) && !empty($measures[$item['measure']])) {
      $measure = $measures[$item['measure']];
    }

    // If a value numerator and denominator are available, convert to decimal.
    $value = '';
    if (isset($item['value_numerator']) && isset($item['value_denominator'])) {
      $value = fraction($item['value_numerator'], $item['value_denominator'])->toDecimal(0, TRUE);
    }

    // If a unit ID is available, load the term name.
    $unit = '';
    if (!empty($item['unit'])) {
      $term = taxonomy_term_load($item['unit']);
      if (!empty($term->name)) {
        $unit = $term->name;
      }
    }

    // If a label is available, pass it through check_plain().
    $label = '';
    if (!empty($item['label'])) {
      $label = check_plain($item['label']);
    }

    // Assemble the output.
    $output_parts = array();
    if (!empty($label)) {
      if (!empty($value) || !empty($unit)) {
        $label .= ':';
      }
      $output_parts[] = $label;
    }
    if (!empty($value)) {
      $output_parts[] = $value;
    }
    if (!empty($unit)) {
      $output_parts[] = $unit;
    }
    if (!empty($measure)) {
      if (!empty($value) || !empty($unit) || !empty($label)) {
        $measure = '(' . $measure . ')';
      }
      $output_parts[] = $measure;
    }
    $output = implode(' ', $output_parts);

    // Create the markup element.
    $element[$delta] = array(
      '#type' => 'markup',
      '#markup' => $output,
    );
  }

  return $element;
}
