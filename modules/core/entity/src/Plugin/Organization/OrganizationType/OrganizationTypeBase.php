<?php

namespace Drupal\farm_entity\Plugin\Organization\OrganizationType;

use Drupal\farm_entity\FarmEntityTypeBase;

/**
 * Provides the base organization type class.
 */
abstract class OrganizationTypeBase extends FarmEntityTypeBase implements OrganizationTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkflowId() {
    return $this->pluginDefinition['workflow'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    return [];
  }

}
