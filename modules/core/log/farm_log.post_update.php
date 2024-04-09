<?php

/**
 * @file
 * Post update hooks for the farm_log module.
 */

use Drupal\log\Entity\LogType;

/**
 * Update core log types to make "done" their default status.
 */
function farm_log_post_update_farm_log_workflow(&$sandbox) {
  /** @var \Drupal\log\Entity\LogType[] $log_types */
  $core_log_types = [
    'activity',
    'birth',
    'harvest',
    'input',
    'lab_test',
    'maintenance',
    'medical',
    'observation',
    'seeding',
    'transplanting',
  ];
  $log_types = LogType::loadMultiple();
  foreach ($log_types as $log_type) {
    if (in_array($log_type->id(), $core_log_types) && $log_type->getWorkflowId() == 'log_default') {
      $log_type->setWorkflowId('farm_log_workflow');
      $log_type->save();
    }
  }
}
