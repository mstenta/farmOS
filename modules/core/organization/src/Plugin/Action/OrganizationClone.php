<?php

namespace Drupal\organization\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\organization\Entity\OrganizationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action that clones an organization.
 *
 * @Action(
 *   id = "organization_clone_action",
 *   label = @Translation("Clone an organization"),
 *   type = "organization"
 * )
 */
class OrganizationClone extends EntityActionBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs an OrganizationClone object.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
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
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(OrganizationInterface $organization = NULL) {
    if ($organization) {
      $cloned_organization = $organization->createDuplicate();
      $new_name = $organization->getName() . ' ' . $this->t('(clone of organization #@id)', ['@id' => $organization->id()]);
      $cloned_organization->setOwnerId($this->currentUser->id());
      $cloned_organization->setName($new_name);
      $cloned_organization->save();
      $this->messenger()->addMessage($this->t('Organization saved: <a href=":uri">%organization_label</a>', [':uri' => $cloned_organization->toUrl()->toString(), '%organization_label' => $cloned_organization->label()]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\organization\Entity\OrganizationInterface $object */
    $result = $object->access('view', $account, TRUE)
      ->andIf($object->access('create', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
