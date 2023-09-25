<?php

namespace Drupal\Tests\farm_import_csv\Kernel;

use Drupal\taxonomy\Entity\Term;

/**
 * Tests for taxonomy term CSV importers.
 *
 * @group farm
 */
class TermCsvImportTest extends CsvImportTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_animal_type',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installConfig(['farm_animal_type']);
  }

  /**
   * Test term CSV importer.
   */
  public function testTermCsvImport() {

    // Run the CSV import.
    $this->importCsv('animal-types.csv', 'csv_taxonomy_term:animal_type');

    // Confirm that terms have been created with the expected values.
    $terms = Term::loadMultiple();
    $this->assertCount(4, $terms);
    $expected_values = [
      1 => [
        'name' => 'Cow',
        'description' => 'Cow description',
      ],
      2 => [
        'name' => 'Pig',
        'description' => 'Pig description',
      ],
      3 => [
        'name' => 'Sheep',
        'description' => 'Sheep description',
      ],
      4 => [
        'name' => 'Galway',
        'description' => 'Large polled white-faced sheep',
        'parent' => 'Sheep',
      ],
    ];
    foreach ($terms as $id => $term) {
      $this->assertEquals('animal_type', $term->bundle());
      $this->assertEquals($expected_values[$id]['name'], $term->label());
      $this->assertEquals($expected_values[$id]['description'], $term->getDescription());
      $this->assertEquals('default', $term->get('description')->format);
      if (!empty($expected_values[$id]['parent'])) {
        $this->assertEquals($expected_values[$id]['parent'], $term->get('parent')->referencedEntities()[0]->label());
      }
      else {
        $this->assertEmpty($term->get('parent')->referencedEntities());
      }
    }
  }

}
