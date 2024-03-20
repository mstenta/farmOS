<?php

namespace Drupal\farm_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\UriWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'uri_string' widget.
 *
 * @FieldWidget(
 *   id = "uri_string",
 *   label = @Translation("URI string field"),
 *   field_types = {
 *     "uri",
 *   }
 * )
 */
class UriStringWidget extends UriWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // The core uri element uses a type of url, which does not support RFC3986.
    // So we just override the type and use a constraint validator instead.
    // @see https://www.drupal.org/project/drupal/issues/3186651
    // @see \Drupal\farm_field\Plugin\Validation\Constraint\UriValidator
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#type'] = 'textfield';
    return $element;
  }

}
