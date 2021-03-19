<?php

namespace Drupal\Tests\farm_inventory\Kernel;

use Drupal\asset\Entity\Asset;
use Drupal\KernelTests\KernelTestBase;
use Drupal\log\Entity\Log;

/**
 * Tests for farmOS inventory logic.
 *
 * @group farm
 */
class InventoryTest extends KernelTestBase {

  /**
   * Asset inventory service.
   *
   * @var \Drupal\farm_inventory\AssetInventoryInterface
   */
  protected $assetInventory;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset',
    'log',
    'farm_field',
    'farm_inventory',
    'farm_inventory_test',
    'farm_log',
    'geofield',
    'state_machine',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->assetInventory = \Drupal::service('asset.inventory');
    $this->installEntitySchema('asset');
    $this->installEntitySchema('log');
    $this->installEntitySchema('user');
    $this->installConfig([
      'farm_inventory_test',
    ]);
  }

  /**
   * Test asset inventory.
   */
  public function testAssetInventory() {

    // Create a new asset.
    /** @var \Drupal\asset\Entity\AssetInterface $asset */
    $asset = Asset::create([
      'type' => 'container',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $asset->save();

    // When an asset has no adjustment logs, it has no inventory.
    $this->assertFalse($this->assetInventory->hasInventory($asset), 'New assets do not have inventory.');
  }

}
