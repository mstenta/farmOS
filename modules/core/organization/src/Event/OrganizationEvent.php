<?php

namespace Drupal\organization\Event;

use Drupal\organization\Entity\OrganizationInterface;
use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired by organization save, delete and clone operations.
 */
class OrganizationEvent extends Event {

  const PRESAVE = 'organization_presave';
  const INSERT = 'organization_insert';
  const UPDATE = 'organization_update';
  const DELETE = 'organization_delete';

  /**
   * The Organization entity.
   *
   * @var \Drupal\organization\Entity\OrganizationInterface
   */
  public OrganizationInterface $organization;

  /**
   * Constructs the object.
   *
   * @param \Drupal\organization\Entity\OrganizationInterface $organization
   *   The Organization entity.
   */
  public function __construct(OrganizationInterface $organization) {
    $this->organization = $organization;
  }

}
