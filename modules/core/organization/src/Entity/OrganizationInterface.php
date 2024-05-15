<?php

namespace Drupal\organization\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining organization entities.
 *
 * @ingroup organization
 */
interface OrganizationInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, EntityOwnerInterface {

  /**
   * Gets the organization name.
   *
   * @return string
   *   The organization name.
   */
  public function getName();

  /**
   * Sets the organization name.
   *
   * @param string $name
   *   The organization name.
   *
   * @return \Drupal\organization\Entity\OrganizationInterface
   *   The organization entity.
   */
  public function setName($name);

  /**
   * Gets the organization creation timestamp.
   *
   * @return int
   *   Creation timestamp of the organization.
   */
  public function getCreatedTime();

  /**
   * Sets the organization creation timestamp.
   *
   * @param int $timestamp
   *   Creation timestamp of the organization.
   *
   * @return \Drupal\organization\Entity\OrganizationInterface
   *   The organization entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the organization archived timestamp.
   *
   * @return int
   *   Archived timestamp of the organization.
   */
  public function getArchivedTime();

  /**
   * Sets the organization archived timestamp.
   *
   * @param int $timestamp
   *   Archived timestamp of the organization.
   *
   * @return \Drupal\organization\Entity\OrganizationInterface
   *   The organization entity.
   */
  public function setArchivedTime($timestamp);

  /**
   * Gets the label of the the organization type.
   *
   * @return string
   *   The label of the organization type.
   */
  public function getBundleLabel();

}
