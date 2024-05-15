<?php

namespace Drupal\organization\Plugin\Action;

use Drupal\organization\Entity\OrganizationInterface;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Session\AccountInterface;

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
   * {@inheritdoc}
   */
  public function execute(OrganizationInterface $organization = NULL) {
    if ($organization) {
      $cloned_organization = $organization->createDuplicate();
      $new_name = $organization->getName() . ' ' . $this->t('(clone of organization #@id)', ['@id' => $organization->id()]);
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
