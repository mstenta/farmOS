<?php

namespace Drupal\farm_quick_observation\Plugin\QuickForm;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormInterface;
use Drupal\farm_quick\Traits\QuickFormElementsTrait;
use Drupal\farm_quick\Traits\QuickLogTrait;
use Psr\Container\ContainerInterface;

/**
 * Observation quick form.
 *
 * @QuickForm(
 *   id = "observation",
 *   label = @Translation("Observation"),
 *   description = @Translation("Record observations."),
 *   helpText = @Translation("Use this form to record observations. A new observation log will be created."),
 *   permissions = {
 *     "create observation log",
 *   }
 * )
 */
class Observation extends QuickFormBase implements QuickFormInterface {

  use QuickFormElementsTrait;
  use QuickLogTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a Harvest object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $messenger);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $id = NULL) {

    // Date.
    $form['date'] = $this->timestampElement();

    // Assets.
    $form['asset'] = $this->assetReferenceElement(
      title: $this->t('Assets'),
      description: $this->t('Which assets are being observed?'),
      multiple: TRUE,
    );

    // Locations.
    $form['location'] = $this->assetReferenceElement(
      title: $this->t('Locations'),
      description: $this->t('Where is this observation taking place?'),
      multiple: TRUE,
      view: 'farm_location_reference:entity_reference'
    );

    // Geometry.
    $form['geometry'] = [
      '#type' => 'farm_map_input',
      '#title' => $this->t('Geometry'),
      '#display_raw_geometry' => TRUE,
    ];

    // Notes.
    $form['notes'] = $this->notesElement();

    // Done.
    $form['done'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Completed'),
      '#default_value' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Draft an observation log from the user-submitted data.
    $timestamp = $form_state->getValue('date')->getTimestamp();
    $status = $form_state->getValue('done') ? 'done' : 'pending';
    $log = [
      'type' => 'observation',
      'timestamp' => $timestamp,
      'asset' => $form_state->getValue('asset'),
      'location' => $form_state->getValue('location'),
      'geometry' => $form_state->getValue('geometry'),
      'notes' => $form_state->getValue('notes'),
      'status' => $status,
    ];

    // Generate a name for the log.
    $log['name'] = $this->t('Observation');

    // Create the log.
    $this->createLog($log);
  }

}
