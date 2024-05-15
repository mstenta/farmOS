<?php

namespace Drupal\Tests\organization\Functional;

use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;

/**
 * Tests the organization CRUD.
 */
abstract class OrganizationTestBase extends FarmBrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'organization',
    'organization_test',
    'entity',
    'user',
    'field',
    'text',
  ];

  /**
   * A test user with administrative privileges.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser($this->getAdministratorPermissions());
    $this->drupalLogin($this->adminUser);
    drupal_flush_all_caches();
  }

  /**
   * Gets the permissions for the admin user.
   *
   * @return string[]
   *   The permissions.
   */
  protected function getAdministratorPermissions() {
    return [
      'access administration pages',
      'administer organizations',
      'view any organization',
      'create default organization',
      'view any default organization',
      'update own default organization',
      'update any default organization',
      'delete own default organization',
      'delete any default organization',
    ];
  }

  /**
   * Creates a organization entity.
   *
   * @param array $values
   *   Array of values to feed the entity.
   *
   * @return \Drupal\organization\Entity\OrganizationInterface
   *   The organization entity.
   */
  protected function createOrganizationEntity(array $values = []) {
    $storage = \Drupal::service('entity_type.manager')->getStorage('organization');
    $entity = $storage->create($values + [
      'name' => $this->randomMachineName(),
      'created' => \Drupal::time()->getRequestTime(),
      'type' => 'default',
    ]);
    return $entity;
  }

}
