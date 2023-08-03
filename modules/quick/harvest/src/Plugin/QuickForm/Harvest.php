<?php

namespace Drupal\farm_quick_harvest\Plugin\QuickForm;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Traits\QuickLogTrait;
use Drupal\farm_quick\Traits\QuickPrepopulateTrait;

/**
 * Harvest quick form.
 *
 * @QuickForm(
 *   id = "harvest",
 *   label = @Translation("Harvest"),
 *   description = @Translation("Record when a harvest takes place."),
 *   helpText = @Translation("Use this form to record a harvest."),
 *   permissions = {
 *     "create harvest log",
 *   }
 * )
 */
class Harvest extends QuickFormBase {

  use QuickLogTrait;
  use QuickPrepopulateTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {

    // Date+time selection field (defaults to now).
    $form['timestamp'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Date'),
      '#default_value' => new DrupalDateTime('now', \Drupal::currentUser()->getTimeZone()),
      '#required' => TRUE,
    ];

    // Asset reference field (allow multiple).
    $form['asset'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Assets'),
      '#target_type' => 'asset',
      '#tags' => TRUE,
      '#default_value' => $this->getPrepopulatedEntities('asset', $form_state),
    ];

    // Harvest quantity field.
    $form['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Quantity'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Draft a harvest log from the user-submitted data.
    $timestamp = $form_state->getValue('timestamp')->getTimestamp();
    $asset = $form_state->getValue('asset');
    $quantity = $form_state->getValue('quantity');
    $log = [
      'type' => 'harvest',
      'timestamp' => $timestamp,
      'asset' => $asset,
      'quantity' => [
        [
          'type' => 'standard',
          'value' => $quantity,
        ],
      ],
      'status' => 'done',
    ];

    // Create the log.
    $this->createLog($log);
  }

}
