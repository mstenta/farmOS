<?php

namespace Drupal\farm_form\Element;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;

/**
 * Form element for referencing assets.
 *
 * This renders a checkboxes form element for selecting asset entities. Below
 * the checkboxes is an autocomplete search textfield which adds assets to the
 * list of options.
 *
 * The #default_value accepts an array of Asset entities.
 *
 * @FormElement("asset_reference")
 */
class AssetReference extends Checkboxes {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $class = static::class;

    // Apply default form element properties.
    $info['#selection_settings'] = [
      'sort' => [
        'field' => 'status',
        'direction' => 'ASC',
      ],
    ];
    $info['#placeholder_text'] = t('Search assets');

    // Process the element with our method first.
    array_unshift($info['#process'], [$class, 'processAssetReference']);

    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {

    // Process the #default_value property.
    if ($input === FALSE && isset($element['#default_value'])) {

      // Convert the default value into an array.
      if (!empty($element['#default_value']) && !is_array($element['#default_value'])) {
        $element['#default_value'] = [$element['#default_value']];
      }

      // Convert to asset IDs.
      foreach ($element['#default_value'] as $key => $asset) {

        // Make sure default values are asset objects.
        if (!($asset instanceof AssetInterface)) {
          throw new \InvalidArgumentException('The #default_value property has to be an asset object or an array of asset objects.');
        }

        // Save the asset ID.
        $element['#default_value'][$key] = $asset->id();
      }
    }

    // If user input was provided, unset the search value.
    if ($input !== FALSE && is_array($input)) {
      unset($input['search']);
    }

    // Delegate to parent method.
    return parent::valueCallback($element, $input, $form_state);
  }

  /**
   * Process asset reference form element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The form element.
   */
  public static function processAssetReference(array &$element, FormStateInterface $form_state, array &$complete_form) {

    // Add an entity_autocomplete search box.
    $element['search'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'asset',
      '#attributes' => [
        'placeholder' => $element['#placeholder_text'],
      ],
      '#selection_settings' => $element['#selection_settings'],
    ];

    // Load selected assets from #value or #default_value, if available.
    // Convert to a list of asset IDs for internal processing.
    /** @var \Drupal\asset\Entity\AssetInterface $assets */
    $assets = [];
    if (!empty($element['#default_value'])) {
      foreach ($element['#default_value'] as $id) {
        $element['#default_value'][$id] = $id;
        $assets[] = \Drupal::entityTypeManager()->getStorage('asset')->load($id);
      }
    }
    if (!empty($element['#value'])) {
      foreach ($element['#value'] as $id) {
        $element['#default_value'][$id] = $id;
        $assets[] = \Drupal::entityTypeManager()->getStorage('asset')->load($id);
      }
    }

    // Build checkbox options.
    // Use the special 'view label' access check, since some entities allow the
    // label to be viewed, even if the entity is not allowed to be viewed.
    $element['#options'] = [];
    if (!empty($assets)) {
      foreach ($assets as $asset) {
        $element['#options'][$asset->id()] = ($asset->access('view label')) ? $asset->label() : t('- Restricted access -');
      }
    }

    // Return the element.
    return $element;
  }

}
