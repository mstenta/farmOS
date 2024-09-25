<?php

namespace Drupal\farm_quick_group\Plugin\QuickForm;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\farm_group\GroupMembershipInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormInterface;
use Drupal\farm_quick\Traits\QuickFormElementsTrait;
use Drupal\farm_quick\Traits\QuickLogTrait;
use Drupal\farm_quick\Traits\QuickPrepopulateTrait;
use Drupal\farm_quick\Traits\QuickStringTrait;
use Psr\Container\ContainerInterface;

/**
 * Group quick form.
 *
 * @QuickForm(
 *   id = "group",
 *   label = @Translation("Group membership"),
 *   description = @Translation("Record asset group membership changes."),
 *   helpText = @Translation("Use this form to assign assets to a group. A new observation log will be created to record the group membership change."),
 *   permissions = {
 *     "create observation log",
 *   }
 * )
 */
class Group extends QuickFormBase implements QuickFormInterface {

  use QuickLogTrait;
  use QuickFormElementsTrait;
  use QuickPrepopulateTrait;
  use QuickStringTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Group membership service.
   *
   * @var \Drupal\farm_group\GroupMembershipInterface
   */
  protected $groupMembership;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a Group object.
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
   * @param \Drupal\farm_group\GroupMembershipInterface $group_membership
   *   Group membership service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger, EntityTypeManagerInterface $entity_type_manager, GroupMembershipInterface $group_membership, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $messenger);
    $this->entityTypeManager = $entity_type_manager;
    $this->groupMembership = $group_membership;
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
      $container->get('group.membership'),
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
    $prepopulated_assets = $this->getPrepopulatedEntities('asset', $form_state);
    $form['asset'] = $this->assetReferenceElement(
      title: $this->t('Assets'),
      description: $this->t('Which assets are changing group membership?'),
      required: TRUE,
      multiple: TRUE,
      default: $prepopulated_assets,
    );

    // Groups.
    $form['group'] = $this->assetReferenceElement(
      title: $this->t('Groups'),
      description: $this->t('The groups to assign the assets to. Leave blank to un-assign assets from all groups.'),
      multiple: TRUE,
      view: 'farm_group_reference:entity_reference',
    );

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

    // Draft a group membership observation log from the user-submitted data.
    $timestamp = $form_state->getValue('date')->getTimestamp();
    $status = $form_state->getValue('done') ? 'done' : 'pending';
    $log = [
      'type' => 'observation',
      'timestamp' => $timestamp,
      'asset' => $form_state->getValue('asset'),
      'group' => $form_state->getValue('group'),
      'notes' => $form_state->getValue('notes'),
      'status' => $status,
      'is_group_assignment' => TRUE,
    ];

    // Load assets and groups.
    $assets = $this->loadReferencedAssets($form_state->getValue('asset'));
    $groups = $this->loadReferencedAssets($form_state->getValue('group'));

    // Generate a name for the log.
    $asset_names = $this->entityLabelsSummary($assets);
    $group_names = $this->entityLabelsSummary($groups);
    $log['name'] = $this->t('Clear group membership of @assets', ['@assets' => Markup::create($asset_names)]);
    if (!empty($group_names)) {
      $log['name'] = $this->t('Group @assets into @groups', ['@assets' => Markup::create($asset_names), '@groups' => Markup::create($group_names)]);
    }

    // Create the log.
    $this->createLog($log);
  }

}
