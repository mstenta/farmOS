<?php

namespace Drupal\organization\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the organization type entity.
 *
 * @ConfigEntityType(
 *   id = "organization_type",
 *   label = @Translation("Organization type"),
 *   label_collection = @Translation("Organization types"),
 *   label_singular = @Translation("Organization type"),
 *   label_plural = @Translation("Organization types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count organization type",
 *     plural = "@count organization types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\organization\OrganizationTypeListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\organization\Form\OrganizationTypeForm",
 *       "edit" = "Drupal\organization\Form\OrganizationTypeForm",
 *       "delete" = "\Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer organization types",
 *   config_prefix = "type",
 *   bundle_of = "organization",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/organization-type/{organization_type}",
 *     "add-form" = "/admin/structure/organization-type/add",
 *     "edit-form" = "/admin/structure/organization-type/{organization_type}/edit",
 *     "delete-form" = "/admin/structure/organization-type/{organization_type}/delete",
 *     "collection" = "/admin/structure/organization-type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "workflow",
 *     "new_revision",
 *   }
 * )
 */
class OrganizationType extends ConfigEntityBundleBase implements OrganizationTypeInterface {

  /**
   * The organization type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The organization type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this organization type.
   *
   * @var string
   */
  protected $description;

  /**
   * The organization type workflow ID.
   *
   * @var string
   */
  protected $workflow;

  /**
   * Default value of the 'Create new revision' checkbox.
   *
   * @var bool
   */
  protected $new_revision = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    return $this->set('description', $description);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // If the organization type id changed, update all existing organizations of
    // that type.
    if ($update && $this->getOriginalId() != $this->id()) {
      $update_count = $this->entityTypeManager()->getStorage('organization')->updateType($this->getOriginalId(), $this->id());
      if ($update_count) {
        \Drupal::messenger()->addMessage(\Drupal::translation()->formatPlural($update_count,
          'Changed the organization type of 1 post from %old-type to %type.',
          'Changed the organization type of @count posts from %old-type to %type.',
          [
            '%old-type' => $this->getOriginalId(),
            '%type' => $this->id(),
          ]));
      }
    }
    if ($update) {
      // Clear the cached field definitions as some settings affect the field
      // definitions.
      $this->entityTypeManager()->clearCachedDefinitions();
      \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkflowId() {
    return $this->workflow;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkflowId($workflow_id) {
    $this->workflow = $workflow_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // The organization type must depend on the module that provides the
    // workflow.
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflow = $workflow_manager->createInstance($this->getWorkflowId());
    $this->calculatePluginDependencies($workflow);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    return $this->set('new_revision', $new_revision);
  }

}
