<?php

namespace Drupal\farm_role;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccessPolicyProcessorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\PermissionChecker;

/**
 * Checks permissions for an account.
 */
class ManagedRolePermissionChecker extends PermissionChecker {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The managed role permissions manager.
   *
   * @var \Drupal\farm_role\ManagedRolePermissionsManagerInterface
   */
  protected $managedRolePermissionsManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|\Drupal\Core\Session\AccessPolicyProcessorInterface $processor
   *   The access policy processor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\farm_role\ManagedRolePermissionsManagerInterface $managed_role_permissions_manager
   *   The managed role permissions manager.
   */
  public function __construct(protected EntityTypeManagerInterface|AccessPolicyProcessorInterface $processor, EntityTypeManagerInterface $entity_type_manager, ManagedRolePermissionsManagerInterface $managed_role_permissions_manager) {
    parent::__construct($processor);
    $this->entityTypeManager = $entity_type_manager;
    $this->managedRolePermissionsManager = $managed_role_permissions_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission(string $permission, AccountInterface $account): bool {
    $has_permission = parent::hasPermission($permission, $account);

    // Check if the permission is included via farm_role rules.
    if (!$has_permission) {
      $managed_roles = $this->managedRolePermissionsManager->getMangedRoles();
      foreach ($account->getRoles() as $role_id) {
        if (in_array($role_id, array_keys($managed_roles))) {
          /** @var \Drupal\user\RoleInterface $role */
          $role = $this->entityTypeManager->getStorage('user_role')->load($role_id);
          $has_permission = $this->managedRolePermissionsManager->isPermissionInRole($permission, $role);
          if ($has_permission) {
            break;
          }
        }
      }
    }

    return $has_permission;
  }

}
