<?php

namespace Drupal\Tests\farm_quick\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;

/**
 * Tests the quick form framework.
 *
 * @group farm
 */
class QuickFormTest extends FarmBrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_quick_test',
  ];

  /**
   * Test quick forms.
   */
  public function testQuickForms() {

    // Create and login a test user with no permissions.
    $user = $this->createUser();
    $this->drupalLogin($user);

    // Go to the quick form index and confirm that access is denied.
    $this->drupalGet('quick');
    $this->assertSession()->statusCodeEquals(403);

    // Create and login a test user with access to the quick form index.
    $user = $this->createUser(['view quick forms index']);
    $this->drupalLogin($user);

    // Go to the quick form index and confirm that access is granted, but no
    // quick forms are visible.
    $this->drupalGet('quick');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('You do not have any quick forms.'));

    // Go to the test quick form and confirm that access is denied.
    $this->drupalGet('quick/test');
    $this->assertSession()->statusCodeEquals(403);

    // Create and login a test user with access to the quick form index, and
    // permission to create test logs.
    $user = $this->createUser(['view quick forms index', 'create test log']);
    $this->drupalLogin($user);

    // Go to the quick form index and confirm that:
    // 1. access is granted
    // 2. the test quick form item is visible
    // 3. the default configurable_test quick form item is visible
    // 4. the second instance of configurable_test quick form item is visible
    // 5. the requires_entity_test quick form item is NOT visible
    $this->drupalGet('quick');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Test quick form'));
    $this->assertSession()->pageTextContains($this->t('Test configurable quick form'));
    $this->assertSession()->pageTextContains($this->t('Test configurable quick form 2'));
    $this->assertSession()->pageTextNotContains($this->t('Test requiresEntity quick form'));

    // Go to the test quick form and confirm that the test field is visible.
    $this->drupalGet('quick/test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Test field'));

    // Go to the default configurable_test quick form and confirm access is
    // granted and the default value is 100.
    $this->drupalGet('quick/configurable_test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('value="100"');

    // Go to the test configuration form and confirm that access is denied.
    $this->drupalGet('quick/configurable_test/configure');
    $this->assertSession()->statusCodeEquals(403);

    // Create and login a test user with permission to create test logs and
    // permission to configure quick forms.
    $user = $this->createUser(['create test log', 'configure quick forms']);
    $this->drupalLogin($user);

    // Go to the default configurable_test quick form and confirm that the
    // default value field is visible and the default value is 100.
    $this->drupalGet('quick/configurable_test/configure');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Default value'));
    $this->assertSession()->responseContains('value="100"');

    // Go to the configurable_test2 quick form and confirm access is granted and
    // the default value is 500.
    $this->drupalGet('quick/configurable_test2');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('value="500"');

    // Go to the configurable_test2 quick form and confirm that the default
    // value field is visible and the default value is 500.
    $this->drupalGet('quick/configurable_test2/configure');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Default value'));
    $this->assertSession()->responseContains('value="500"');

    // Go to the requires_entity_test quick form and confirm 404 not found.
    $this->drupalGet('quick/requires_entity_test');
    $this->assertSession()->statusCodeEquals(404);
  }

}
