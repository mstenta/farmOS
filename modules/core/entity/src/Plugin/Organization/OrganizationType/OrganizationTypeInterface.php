<?php

namespace Drupal\farm_entity\Plugin\Organization\OrganizationType;

use Drupal\entity\BundlePlugin\BundlePluginInterface;

/**
 * Defines the interface for organization types.
 */
interface OrganizationTypeInterface extends BundlePluginInterface {

  /**
   * Gets the organization type label.
   *
   * @return string
   *   The organization type label.
   */
  public function getLabel();

  /**
   * Gets the organization workflow ID.
   *
   * @return string
   *   The organization workflow ID.
   */
  public function getWorkflowId();

}
