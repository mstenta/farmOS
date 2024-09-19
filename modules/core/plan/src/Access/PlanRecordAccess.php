<?php

declare(strict_types=1);

namespace Drupal\plan\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\plan\Entity\PlanRecordInterface;

/**
 * Defines plan_record access logic.
 */
class PlanRecordAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // If a plan is referenced, access is based on access to the plan.
    if ($entity instanceof PlanRecordInterface && $plan = $entity->getPlan()) {
      return AccessResult::allowedIf($plan->access($operation, $account));
    }

    // Otherwise, delegate to the parent method.
    return parent::checkAccess($entity, $operation, $account);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {

    // Create access is allowed here since we do not provide permissions for
    // plan_record entities. Access is further restricted in checkFieldAccess().
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, ?FieldItemListInterface $items = NULL) {

    // Only allow creating or updating the plan_record if the user has access
    // to update the plan that the plan_record is referencing.
    // @todo delete operation?
    // @todo check parent method?
    $entity = $items ? $items->getEntity() : NULL;
    if ($entity && $field_definition->getName() === 'plan' && $operation === 'edit') {
      if (!$items->isEmpty() && $plans = $items->referencedEntities()) {
        /** @var \Drupal\plan\Entity\PlanInterface $plan */
        $plan = reset($plans);
        return AccessResult::allowedIf($plan->access('update', $account));
      }
      else {
        return AccessResult::forbidden();
      }
    }
    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

}
