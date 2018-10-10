<?php
/**
 * @file
 * Farm dashboard hooks implemented by farm quick module.
 */

/**
 * Implements hook_farm_dashboard_panes().
 */
function farm_quick_farm_dashboard_panes() {
  return array(
    'farm_quick_forms' => array(
      'title' => t('Quick forms'),
      'callback' => 'farm_quick_dashboard',
      'group' => 'quick',
    ),
  );
}

/**
 * Quick forms dashboard callback.
 */
function farm_quick_dashboard() {

  // Start with empty output.
  $output = '';

  // Ask for quick forms from modules.
  $forms = farm_quick_forms();

  // Get a list of enabled quick forms from saved variable.
  $enabled_quick_forms = variable_get('farm_quick_forms_enabled', array());

  // Filter out disabled forms.
  foreach ($forms as $name => $info) {
    if (empty($enabled_quick_forms[$name])) {
      unset($forms[$name]);
    }
  }

  // Iterate through the forms and build a list of links.
  $links = array();
  foreach ($forms as $name => $form) {
    $links[] = l($form['label'], 'farm/quick/' . $name);
  }

  // If there are links, render them in a list.
  if (!empty($links)) {
    $output .= theme('item_list', array('items' => $links));
  }

  // Add a link to configure quick forms.
  $output .= '<p>' . l(t('Configure quick forms'), 'farm/quick/configure') . '</p>';

  // Return the output.
  return $output;
}
