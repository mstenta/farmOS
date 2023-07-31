<?php

namespace Drupal\farm_quick_soil_test\Plugin\QuickForm;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormInterface;
use Drupal\farm_quick\Traits\QuickFormElementsTrait;
use Psr\Container\ContainerInterface;

/**
 * Soil test quick form.
 *
 * @QuickForm(
 *   id = "soil_test",
 *   label = @Translation("Soil test"),
 *   description = @Translation("Record the results of a soil test."),
 *   helpText = @Translation("Use this form to record the results of a laboratory soil test."),
 *   permissions = {
 *     "create lab_test log",
 *   }
 * )
 */
class SoilTest extends QuickFormBase implements QuickFormInterface {

  use QuickFormElementsTrait;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a QuickFormBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $messenger);
    $this->messenger = $messenger;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Laboratory.
    $form['lab'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Laboratory'),
      '#description' => $this->t('What is the name of the laboratory that performed this test?'),
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => [
        'target_bundles' => ['lab'],
      ],
      '#autocreate' => [
        'bundle' => 'lab',
      ],
    ];

    // Inline dates container.
    $form['date'] = $this->buildInlineContainer();

    // Sample date.
    $form['date']['sample_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Sample date'),
      '#description' => $this->t('When was the soil sample collected?'),
      '#default_value' => new DrupalDateTime('midnight', $this->currentUser->getTimeZone()),
      '#required' => TRUE,
    ];

    // Lab received date.
    $form['date']['received_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Received date'),
      '#description' => $this->t('When was the soil sample received by the lab?'),
    ];

    // Lab processed date.
    $form['date']['processed_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Processed date'),
      '#description' => $this->t('When was the soil sample processed by the lab?'),
    ];

    // Location.
    $form['location'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Land asset'),
      '#description' => $this->t('Associate this lab test with a land asset.'),
      '#target_type' => 'asset',
      '#selection_handler' => 'views',
      '#selection_settings' => [
        'target_bundles' => ['land'],
        'sort' => [
          'field' => 'status',
          'direction' => 'ASC',
        ],
      ],
      '#maxlength' => 1024,
      '#tags' => TRUE,
    ];

    // Sample location.
    $form['geometry'] = [
      '#type' => 'farm_map_input',
      '#title' => $this->t('Sample location'),
      '#description' => $this->t('Where were the soil samples collected from?'),
      '#map_settings' => [
        'behaviors' => [
          'nrcs_soil_survey' => [
            'visible' => TRUE,
          ],
        ],
      ],
      '#display_raw_geometry' => TRUE,
    ];

    // Latitude/longitude fields.
    $form['latlon'] = [
      '#type' => 'details',
      '#title' => $this->t('Latitude/Longitude'),
      '#description' => $this->t('If you know the latitude/longitude of the point where the same was taken from, you can enter them here to add a point to the map.'),
    ];
    $form['latlon']['lat'] = [
      '#type' => 'number',
      '#title' => $this->t('Latitude'),
      '#min' => -90,
      '#max' => 90,
      '#step' => 0.00000000000001,
    ];
    $form['latlon']['lon'] = [
      '#type' => 'number',
      '#title' => $this->t('Longitude'),
      '#min' => -180,
      '#max' => 180,
      '#step' => 0.00000000000001,
    ];
    $form['latlon']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to map'),
      // @todo
    ];

    $form['results'] = [
      '#type' => 'details',
      '#title' => $this->t('Test results'),
      '#open' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // @todo only allow point geometries
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo
  }

}
