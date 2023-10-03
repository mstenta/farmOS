<?php

namespace Drupal\farm_form\Element;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Form element for referencing assets.
 *
 * @FormElement("asset_reference")
 */
class AssetReference extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#process' => [
        [$class, 'processElement'],
      ],
    ];
  }

  public static function processElement(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    // Create a wrapper with a unique ID.
    $id_prefix = implode('-', $element['#parents']);
    $wrapper_id = Html::getUniqueId($id_prefix . '-ajax-wrapper');
    $element['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['#suffix'] = '</div>';

    // Add asset from search field input to default values.
    $search_value = $form_state->getValue(array_merge($element['#array_parents'], ['search']));
    if (!empty($search_value)) {
      $asset_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($search_value);
      if (!empty($asset_id)) {
        $element['#default_value'][] = $asset_id;
      }
    }

    // Build a list of checkbox options from default value.
    $asset_options = [];
    if (!empty($element['#default_value']) && is_array($element['#default_value'])) {
      $asset_ids = [];
      foreach ($element['#default_value'] as $value) {
        if (is_numeric($value)) {
          $asset_ids[] = $value;
        }
      }
      $assets = \Drupal::service('entity_type.manager')->getStorage('asset')->loadMultiple($asset_ids);
      $asset_options[] = array_map(function (AssetInterface $asset) {
        return $asset->label();
      }, $assets);
    }

    // Add checkboxes element, with all options checked.
    $element['checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => $element['#title'],
      '#description' => $element['#description'],
      '#options' => $asset_options,
      '#default_value' => $element['#default_value'],
    ];

    # Entity autocomplete search.
    $element['search'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'asset',
      '#selection_settings' => [
        'sort' => [
          'field' => 'status',
          'direction' => 'ASC',
        ],
      ],
      '#ajax' => [
        'callback' => [get_called_class(), 'ajaxRefresh'],
        'wrapper' => $wrapper_id,
        'event' => 'autocompleteclose change',
      ],
    ];

    // Return the element.
    return $element;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = $triggering_element['#array_parents'];
    array_pop($parents);
    $element = NestedArray::getValue($form, $parents);
    return $element;
  }

}
