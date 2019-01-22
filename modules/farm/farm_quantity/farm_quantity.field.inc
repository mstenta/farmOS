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
