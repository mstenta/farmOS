<?php

namespace Drupal\Tests\farm_farm\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;

/**
 * Tests the farm reference field.
 *
 * @group farm
 */
class FarmFieldTest extends FarmBrowserTestBase {

  use StringTranslationTrait;

  /**
   * Test user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_entity',
    'farm_farm',
    'farm_farm_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create and login a user with permission to administer assets and
    // organizations.
    $this->user = $this->createUser(['administer assets', 'administer organizations']);
    $this->drupalLogin($this->user);
  }

  /**
   * Test that the Farm reference field is added to assets.
   */
  public function testFarmField() {
    $entity_type_manager = $this->container->get('entity_type.manager');
    $asset_storage = $entity_type_manager->getStorage('asset');
    $organization_storage = $entity_type_manager->getStorage('organization');

    // Create a farm organization.
    $farm = $organization_storage->create([
      'name' => $this->randomMachineName(),
      'type' => 'farm',
    ]);
    $farm->save();

    // Go to the asset add form.
    $this->drupalGet('asset/add/test');
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the farm reference field form is visible.
    $this->assertSession()->fieldExists('farm[]');
    $this->assertSession()->pageTextContains('What farm is this associated with?');

    // Create an asset that references the farm.
    $asset = $asset_storage->create(['type' => 'test']);
    $asset->farm[] = ['target_id' => $farm->id()];
    $asset->save();

    // Go to the asset view page.
    $this->drupalGet('asset/' . $asset->id());
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the farm reference field display is visible.
    $this->assertSession()->pageTextContains('Farm');
    $this->assertSession()->pageTextContains($farm->label());
  }

}
