<?php

namespace Drupal\organization\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\state_machine\WorkflowManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for organization type entities.
 *
 * @package Drupal\organization\Form
 */
class OrganizationTypeForm extends EntityForm {

  /**
   * The workflow manager.
   *
   * @var \Drupal\state_machine\WorkflowManagerInterface
   */
  protected $workflowManager;

  /**
   * Constructs a new OrganizationTypeForm object.
   *
   * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
   *   The workflow manager.
   */
  public function __construct(WorkflowManagerInterface $workflow_manager) {
    $this->workflowManager = $workflow_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.workflow')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $organization_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $organization_type->label(),
      '#description' => $this->t('Label for the organization type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $organization_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\organization\Entity\OrganizationType::load',
      ],
      '#disabled' => !$organization_type->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $organization_type->getDescription(),
    ];

    $form['workflow'] = [
      '#type' => 'select',
      '#title' => $this->t('Workflow'),
      '#options' => $this->workflowManager->getGroupedLabels('organization'),
      '#default_value' => $organization_type->getWorkflowId(),
      '#description' => $this->t('Used by all organizations of this type.'),
    ];

    $form['new_revision'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create new revision'),
      '#default_value' => $organization_type->shouldCreateNewRevision(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $organization_type = $this->entity;
    $status = $organization_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label organization type.', [
          '%label' => $organization_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label organization type.', [
          '%label' => $organization_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($organization_type->toUrl('collection'));

    return $status;
  }

}
