<?php

namespace Drupal\plan\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\plan\Entity\PlanInterface;

/**
 * Action that archives an plan.
 *
 * @Action(
 *   id = "plan_archive_action",
 *   label = @Translation("Archive an plan"),
 *   type = "plan"
 * )
 */
class PlanArchive extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(PlanInterface $plan = NULL) {
    if ($plan) {
      $plan->get('status')->first()->applyTransitionById('archive');
      $plan->setNewRevision(TRUE);
      $plan->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\plan\Entity\PlanInterface $object */
    $result = $object->get('status')->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
