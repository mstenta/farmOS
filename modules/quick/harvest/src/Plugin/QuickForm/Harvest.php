<?php

namespace Drupal\farm_quick_harvest\Plugin\QuickForm;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_quick\Plugin\QuickForm\ConfigurableQuickFormInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Traits\ConfigurableQuickFormTrait;
use Drupal\farm_quick\Traits\QuickLogTrait;

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
class Harvest extends QuickFormBase implements ConfigurableQuickFormInterface {

  use ConfigurableQuickFormTrait;
  use QuickLogTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'default_quantity' => 100,
    ];
  }

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
    ];

    // Harvest quantity field.
    $form['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Quantity'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['default_quantity'],
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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    // Default quantity configuration.
    $form['default_quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Default quantity'),
      '#default_value' => $this->configuration['default_quantity'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['default_quantity'] = $form_state->getValue('default_quantity');
  }

}
