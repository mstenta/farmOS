<?php

namespace Drupal\Tests\organization\Traits;

use Drupal\organization\Entity\Organization;

/**
 * Provides methods to create organization entities.
 *
 * This trait is meant to be used only by test classes.
 */
trait OrganizationCreationTrait {

  /**
   * Creates an organization entity.
   *
   * @param array $values
   *   Array of values to feed the entity.
   *
   * @return \Drupal\organization\Entity\OrganizationInterface
   *   The organization entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createOrganizationEntity(array $values = []) {
    /** @var \Drupal\organization\Entity\OrganizationInterface $entity */
    $entity = Organization::create($values + [
      'name' => $this->randomMachineName(),
      'type' => 'default',
    ]);
    $entity->save();
    return $entity;
  }

}
