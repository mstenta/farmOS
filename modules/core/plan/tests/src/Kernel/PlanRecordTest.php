<?php

declare(strict_types=1);

namespace Drupal\Tests\plan\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\plan\Entity\Plan;
use Drupal\plan\Entity\PlanRecord;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests for plan_record entities.
 *
 * @group farm
 */
class PlanRecordTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity',
    'plan',
    'plan_test',
    'user',
    'state_machine',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('plan');
    $this->installEntitySchema('plan_record');
    $this->installEntitySchema('user');
    $this->installConfig(['plan_test']);
  }

  /**
   * Test plan_record entities.
   */
  public function testPlanRecord() {

    // Get storage for plan and plan_record entities.
    $plan_storage = \Drupal::entityTypeManager()->getStorage('plan');
    $plan_record_storage = \Drupal::entityTypeManager()->getStorage('plan_record');

    // Create a plan entity.
    $plan = Plan::create([
      'name' => 'Test plan',
      'type' => 'default',
    ]);
    $plan->save();

    // Confirm that the plan entity was created.
    $plans = $plan_storage->loadMultiple();
    $this->assertCount(1, $plans);

    // Create two plan_record entities that reference the plan.
    $plan_record1 = PlanRecord::create([
      'plan' => $plan,
      'type' => 'default',
    ]);
    $plan_record1->save();
    $plan_record2 = PlanRecord::create([
      'plan' => $plan,
      'type' => 'default',
    ]);
    $plan_record2->save();

    // Confirm that the plan_record entities were created.
    $plan_records = $plan_record_storage->loadMultiple();
    $this->assertCount(2, $plan_records);

    // Delete the plan.
    $plan->delete();

    // Confirm that the plan and plan_record entities were all deleted.
    $plans = $plan_storage->loadMultiple();
    $this->assertCount(0, $plans);
    $plan_records = $plan_record_storage->loadMultiple();
    $this->assertCount(0, $plan_records);
  }

  /**
   * Test plan_record access logic.
   */
  public function testPlanRecordAccess() {

    // Get storage for plan and plan_record entities.
    $plan_storage = \Drupal::entityTypeManager()->getStorage('plan');
    $plan_record_storage = \Drupal::entityTypeManager()->getStorage('plan_record');

    // Draft a plan_record entity that is not connected to any plan.
    $plan_record = $plan_record_storage->create([
      'type' => 'default',
    ]);

    // Confirm that a plan_record without a plan reference does not validate.
    // This will prevent plan_records from being created where validation is
    // performed (eg: JSON:API).
    $this->assertNotEmpty($plan_record->validate());

    // Create a plan entity and reference it from the plan_record.
    $plan = Plan::create([
      'name' => 'Test plan without author',
      'type' => 'default',
    ]);
    $plan->save();
    $plan_record->set('plan', $plan);

    // Confirm that access is denied to create the plan_record, because the
    // current user (anonymous) does not have access to the plan.
    $this->assertFalse($plan_record->access('create'));

    // Create and login a user with access to plans.
    $user = $this->createUser([
      'create default plan',
      'view any default plan',
      'update any default plan',
      'delete any default plan',
    ]);
    $this->setCurrentUser($user);

    // Confirm that create access is now allowed.
    $this->assertTrue($plan_record->access('create'));
  }

}
