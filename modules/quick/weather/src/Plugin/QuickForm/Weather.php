<?php

namespace Drupal\farm_quick_weather\Plugin\QuickForm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;

/**
 * Weather quick form.
 *
 * @QuickForm(
 *   id = "weather",
 *   label = @Translation("Weather event"),
 *   description = @Translation("Record when a weather event occurs."),
 *   helpText = @Translation("Use this form to record when a weather event occurs."),
 *   permissions = {
 *     "create observation log",
 *   }
 * )
 */
class Weather extends QuickFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {

    // Quantity.
    $form['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Quantity'),
      '#min' => 0,
      '#step' => 1,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
