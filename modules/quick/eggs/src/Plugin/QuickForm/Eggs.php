<?php

namespace Drupal\farm_quick_eggs\Plugin\QuickForm;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Traits\QuickLogTrait;

/**
 * Egg harvest quick form.
 *
 * @QuickForm(
 *   id = "eggs",
 *   label = @Translation("Egg harvest"),
 *   description = @Translation("Record when eggs are harvested."),
 *   helpText = @Translation("Use this form to record when eggs are havested."),
 *   permissions = {
 *     "create harvest log",
 *   }
 * )
 */
class Eggs extends QuickFormBase {

  use QuickLogTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {

    // Date.
    $form['date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date'),
      '#default_value' => new DrupalDateTime('midnight'),
      '#required' => TRUE,
    ];

    // Egg quantity.
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

    // Draft an egg harvest log from the user-submitted data.
    $timestamp = $form_state->getValue('date')->getTimestamp();
    $quantity = $form_state->getValue('quantity');
    $log = [
      'name' => $this->t('Collected @count egg(s)', ['@count' => $quantity]),
      'type' => 'harvest',
      'timestamp' => $timestamp,
      'quantity' => [
        [
          'type' => 'standard',
          'measure' => 'count',
          'value' => $quantity,
          'units' => 'egg(s)',
        ],
      ],
      'status' => 'done',
    ];

    // Create the log.
    $this->createLog($log);
  }

}
