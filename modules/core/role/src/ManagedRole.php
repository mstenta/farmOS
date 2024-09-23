<?php

namespace Drupal\farm_role;

use Drupal\user\Entity\Role;

/**
 * Defines the user role entity class.
 *
 * @ConfigEntityType(
 *   id = "user_role",
 *   label = @Translation("Role"),
 *   label_collection = @Translation("Roles"),
 *   label_singular = @Translation("role"),
 *   label_plural = @Translation("roles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count role",
 *     plural = "@count roles",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\user\RoleStorage",
 *     "access" = "Drupal\user\RoleAccessControlHandler",
 *     "list_builder" = "Drupal\user\RoleListBuilder",
 *     "form" = {
 *       "default" = "Drupal\user\RoleForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer permissions",
 *   config_prefix = "role",
 *   static_cache = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "weight" = "weight",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "/admin/people/roles/manage/{user_role}/delete",
 *     "edit-form" = "/admin/people/roles/manage/{user_role}",
 *     "edit-permissions-form" = "/admin/people/permissions/{user_role}",
 *     "collection" = "/admin/people/roles",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "is_admin",
 *     "permissions",
 *   }
 * )
 */
class ManagedRole extends Role {

  /**
   * {@inheritdoc}
   */
  public function getPermissions() {
    return $this->permissions + \Drupal::service('plugin.manager.managed_role_permissions')->getManagedPermissionsForRole($this);
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission) {
    return parent::hasPermission($permission) || \Drupal::service('plugin.manager.managed_role_permissions')->isPermissionInRole($permission, $this);
  }

}
