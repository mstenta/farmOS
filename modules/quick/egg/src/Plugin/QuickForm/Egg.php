<?php

namespace Drupal\farm_quick_egg\Plugin\QuickForm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Traits\QuickPrepopulateTrait;
use Drupal\farm_quick\Traits\QuickLogTrait;

/**
 * Egg harvest quick form.
 *
 * @QuickForm(
 *   id = "egg",
 *   label = @Translation("Egg harvest"),
 *   description = @Translation("Record when eggs are harvested."),
 *   helpText = @Translation("Use this form to record when eggs are havested."),
 *   permissions = {
 *     "create harvest log",
 *   }
 * )
 */
class Egg extends QuickFormBase {

  use QuickPrepopulateTrait;
  use QuickLogTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {

    // Egg quantity.
    $form['quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Quantity'),
      '#min' => 0,
      '#step' => 1,
      '#required' => TRUE,
    ];

    // Egg layer(s) asset reference.
    // @todo Figure out which assets to present as options.
    $assets = $this->getPrepopulatedEntities('asset');
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
    $quantity = $form_state->getValue('quantity');
    $log = [
      'name' => $this->t('Collected @count egg(s)', ['@count' => $quantity]),
      'type' => 'harvest',
      'quantity' => [
        [
          'measure' => 'count',
          'value' => $quantity,
          'units' => $this->t('egg(s)'),
        ],
      ],
    ];

    // Reference assets, if specified.
    if ($asset = $form_state->getValue('asset')) {
      $log['asset'] = $asset;
    }

    // Create the log.
    $this->createLog($log);
  }

}
