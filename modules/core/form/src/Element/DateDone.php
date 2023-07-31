<?php

namespace Drupal\farm_form\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Form element that renders a datetime and done checkbox inline.
 *
 * @FormElement("date_done")
 */
class DateDone extends FormElement {

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

  /**
   * Generates the form element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element. Note that $element must be taken by reference here, so processed
   *   child elements are taken over into $form_state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processElement(array $element, FormStateInterface $form_state, array &$complete_form) {
    $element['#tree'] = TRUE;
    $element['#prefix'] = '<div class="form-items-inline">';
    $element['#suffix'] = '</div>';
    $element['date'] = [
      '#type' => 'datetime',
      '#title' => $element['#title'] ?? t('Date'),
    ];
    $element['done'] = [
      '#type' => 'checkbox',
      '#title' => t('Completed'),
    ];
    $element['#attached']['library'][] = 'farm_form/date_done';
    return $element;
  }

}
