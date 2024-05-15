<?php

namespace Drupal\organization;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity\BulkFormEntityListBuilder;

/**
 * Defines a class to build a listing of organization entities.
 *
 * @ingroup organization
 */
class OrganizationListBuilder extends BulkFormEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Organization ID');
    $header['label'] = $this->t('Label');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\organization\Entity\OrganizationInterface $entity */
    $row['id'] = ['#markup' => $entity->id()];
    $row['name'] = $entity->toLink($entity->label(), 'canonical')->toRenderable();
    $row['type'] = ['#markup' => $entity->getBundleLabel()];
    return $row + parent::buildRow($entity);
  }

}
