<?php

namespace Drupal\Tests\farm_import_csv\Kernel;

use Drupal\asset\Entity\Asset;

/**
 * Tests for asset CSV importers.
 *
 * @group farm
 */
class AssetCsvImportTest extends CsvImportTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_equipment',
    'farm_id_tag',
    'farm_parent',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installConfig(['farm_equipment']);

    // Add an asset to test parent relationship.
    $asset = Asset::create(['name' => 'Test parent', 'type' => 'equipment', 'status' => 'active']);
    $asset->save();
  }

  /**
   * Test asset CSV importer.
   */
  public function testAssetCsvImport() {

    // Run the CSV import.
    $this->importCsv('equipment.csv', 'csv_asset:equipment');

    // Confirm that 3 assets have been created with the expected values
    // (in addition to the 1 we created in setUp() above).
    $assets = Asset::loadMultiple();
    $this->assertCount(4, $assets);
    $expected_values = [
      2 => [
        'name' => 'Old tractor',
        'parents' => [],
        'notes' => 'Inherited from Grandpa',
        'status' => 'archived',
      ],
      3 => [
        'name' => 'New tractor',
        'parents' => [],
        'notes' => 'Purchased recently',
        'status' => 'active',
      ],
      4 => [
        'name' => 'Baler',
        'parents' => [
          'Test parent',
        ],
        'notes' => 'Makes big bales',
        'status' => 'active',
      ],
    ];
    foreach ($assets as $id => $asset) {
      // Skip assets created in setup().
      if ($id <= 1) {
        continue;
      }
      $this->assertEquals('equipment', $asset->bundle());
      $this->assertEquals($expected_values[$id]['name'], $asset->label());
      $parents = $asset->get('parent')->referencedEntities();
      $this->assertEquals(count($expected_values[$id]['parents']), count($parents));
      foreach ($parents as $parent) {
        $this->assertContains($parent->label(), $expected_values[$id]['parents']);
      }
      $this->assertEquals($expected_values[$id]['notes'], $asset->get('notes')->value);
      $this->assertEquals('default', $asset->get('notes')->format);
      $this->assertEquals($expected_values[$id]['status'], $asset->get('status')->value);
      $this->assertEquals('Imported via CSV.', $asset->getRevisionLogMessage());
    }
  }

}
