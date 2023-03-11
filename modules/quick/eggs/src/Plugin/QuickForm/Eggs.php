<?php

namespace Drupal\farm_quick_eggs\Plugin\QuickForm;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_quick\Plugin\QuickForm\ConfigurableQuickFormInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Traits\ConfigurableQuickFormTrait;
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
class Eggs extends QuickFormBase implements ConfigurableQuickFormInterface {

  use ConfigurableQuickFormTrait;
  use QuickLogTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'units' => 'egg(s)',
    ];
  }

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
      '#description' => $this->t('Enter the total quantity collected. This will be recorded as a count with a measurement unit of @units.', ['@units' => $this->configuration['units']]),
      '#min' => 0,
      '#step' => 1,
      '#required' => TRUE,
    ];

    // Egg layer(s) asset reference.
    // @todo Figure out which assets to present as options.
    $assets = [];
    if (!empty($assets)) {
      $asset_options = [];
      foreach ($assets as $asset) {
        $asset_options[$asset->id()] = $asset->label();
      }
      $form['asset'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Egg layer(s)'),
        '#options' => $asset_options,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Draft an egg harvest log from the user-submitted data.
    $timestamp = $form_state->getValue('date')->getTimestamp();
    $quantity = $form_state->getValue('quantity');
    $units = $this->configuration['units'];
    $log = [
      'name' => $this->t('Collected @count @units', ['@count' => $quantity, '@units' => $units]),
      'type' => 'harvest',
      'timestamp' => $timestamp,
      'quantity' => [
        [
          'type' => 'standard',
          'measure' => 'count',
          'value' => $quantity,
          'units' => $this->configuration['units'],
        ],
      ],
      'status' => 'done',
    ];

    // Reference assets, if specified.
    if (!empty($form_state->getValue('asset'))) {
      // @todo Reference assets.
    }

    // Create the log.
    $this->createLog($log);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    # Units.
    $form['units'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Units'),
      '#description' => $this->t('Define the measurement units for egg harvest logs. A new unit taxonomy term will be created on demand if necessary.'),
      '#default_value' => $this->configuration['units'],
      '#required' => TRUE,
    ];

    # Add a layer asset.
    $form['add_asset'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Add layer asset'),
      '#description' => $this->t('Search for assets that lay eggs to add them to the quick form.'),
      '#target_type' => 'asset',
      '#selection_settings' => [
        'target_bundles' => ['animal'],
        'sort' => [
          'field' => 'status',
          'direction' => 'ASC',
        ],
      ],
    ];

    // If the group asset module is enabled, also allow referencing groups.
    if (\Drupal::moduleHandler()->moduleExists('farm_group')) {
      $form['add_asset']['#selection_settings']['target_bundles'][] = 'group';
    }

    # Layer assets.
    $form['assets'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Layer asset options'),
      '#description' => $this->t('These assets will be shown in the egg harvest quick form so that harvests can optionally be associated with them.'),
      '#options' => [],
      '#default_value' => [],
    ];

    return $form;
  }

}
