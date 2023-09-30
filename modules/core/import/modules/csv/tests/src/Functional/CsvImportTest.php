<?php

namespace Drupal\Tests\farm_import_csv\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;

/**
 * Tests the farmOS CSV importers.
 *
 * @group farm
 */
class CsvImportTest extends FarmBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_import_csv_test',
  ];

  /**
   * Test CSV importers.
   */
  public function testCsvImporters() {

    // Create and login a test user with no permissions.
    $user = $this->createUser();
    $this->drupalLogin($user);

    // Go to the CSV importer index and confirm that access is denied.
    $this->drupalGet('import/csv');
    $this->assertSession()->statusCodeEquals(403);

    // Create and login a test user with access to view importers.
    $user = $this->createUser(['access farm import index']);
    $this->drupalLogin($user);

    // Go to the CSV importer index and confirm that access is granted, but no
    // importers are visible.
    $this->drupalGet('import/csv');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('You do not have any importers.');

    // Go to the individual log importers and confirm that access is denied.
    $this->drupalGet('import/csv/asset:equipment');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('import/csv/log:harvest');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('import/csv/taxonomy_term:animal_type');
    $this->assertSession()->statusCodeEquals(403);

    // Create and login a test user with access to the CSV importer index, and
    // permission to create equipment assets, harvest logs, and animal type
    // terms.
    $user = $this->createUser([
      'access farm import index',
      'create equipment asset',
      'create harvest log',
      'create terms in animal_type',
    ]);
    $this->drupalLogin($user);

    // Go to the CSV importer index and confirm that access is granted and the
    // individual importers are visible.
    $this->drupalGet('import/csv');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Asset: Equipment');
    $this->assertSession()->pageTextContains('Log: Harvest');
    $this->assertSession()->pageTextContains('Taxonomy Term: Animal type');

    // Go to the harvest log importer and confirm that:
    // 1. access is granted.
    // 2. the title is visible.
    // 3. the migrate_source_ui "Migrations" dropdown is hidden.
    // 4. the migrate_source_ui "Update existing records" checkbox is hidden.
    // 5. the submit button is not titled "Migrate".
    $this->drupalGet('import/csv/log:harvest');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Import Log: Harvest');
    $this->assertSession()->pageTextNotContains('Migrations');
    $this->assertSession()->pageTextNotContains('Update existing records');
    $this->assertSession()->pageTextNotContains('Migrate');

    // Go to the asset, log, and term importers and confirm that column
    // descriptions are included.
    $this->drupalGet('import/csv/asset:equipment');
    $this->assertSession()->statusCodeEquals(200);
    $log_columns = [
      'name: Name of the asset (required).',
      'notes: Notes about the asset.',
      'status: Status of the asset.',
    ];
    foreach ($log_columns as $description) {
      $this->assertSession()->pageTextContains($description);
    }
    $this->drupalGet('import/csv/log:harvest');
    $this->assertSession()->statusCodeEquals(200);
    $log_columns = [
      'name: Name of the log.',
      'timestamp: Timestamp of the log. This can understand most date/time formats.',
      'quantity measure: Measure of the quantity.',
      'quantity value: Numeric value of the quantity.',
      'quantity units: Units of measurement of the quantity. A new term in the units taxonomy will be created if necessary.',
      'quantity label: Label of the quantity.',
      'notes: Notes about the log.',
      'status: Status of the log.',
    ];
    foreach ($log_columns as $description) {
      $this->assertSession()->pageTextContains($description);
    }
    $this->drupalGet('import/csv/taxonomy_term:animal_type');
    $this->assertSession()->statusCodeEquals(200);
    $log_columns = [
      'name: Name of the term (required).',
      'description: Description of the term.',
      'parent: Parent term in the taxonomy hierarchy.',
    ];
    foreach ($log_columns as $description) {
      $this->assertSession()->pageTextContains($description);
    }
  }

}
