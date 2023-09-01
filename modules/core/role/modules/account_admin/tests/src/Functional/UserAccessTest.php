<?php

namespace Drupal\Tests\farm_role_account_admin\Functional;

use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;

/**
 * Tests access to user 1.
 *
 * @group farmier
 */
class UserAccessTest extends FarmBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_role_account_admin',
  ];

  /**
   * Test user 1 access.
   */
  public function testUser1Access() {

    // Create and login a user with farm_account_admin role.
    $user = $this->createUser();
    $user->addRole('farm_account_admin');
    $user->save();
    $this->drupalLogin($user);

    // Confirm that the user cannot access user 1.
    $this->drupalGet('user/1');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('user/1/edit');
    $this->assertSession()->statusCodeEquals(403);

    // Create a second user with farm_account_admin role.
    $user2 = $this->createUser();
    $user2->addRole('farm_account_admin');
    $user2->save();

    // Confirm that the first farm_account_admin user cannot edit the second
    // farm_account_admin user.
    $this->drupalGet('user/1/edit');
    $this->assertSession()->statusCodeEquals(403);

    // Enable the allow_peer_edit setting.
    $settings = \Drupal::configFactory()->getEditable('farm_role_account_admin.settings');
    $settings->set('allow_peer_edit', TRUE);
    $settings->save();

    // Confirm that the first farm_account_admin user can edit the second
    // farm_account_admin user.
    $this->drupalGet('user/1/edit');
    $this->assertSession()->statusCodeEquals(200);
  }

}
