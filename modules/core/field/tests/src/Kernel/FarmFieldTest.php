<?php

namespace Drupal\Tests\farm_field\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests farmOS field factory.
 *
 * @group farm
 */
class FarmFieldTest extends KernelTestBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'farm';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset',
    'farm_field',
    'farm_field_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();
    $this->entityFieldManager = $this->container->get('entity_field.manager');
  }

  /**
   * Test farmOS field factory.
   */
  public function testFieldFactory() {

    // Get test field info.
    $field_info = farm_field_test_fields();
    $this->assertCount(16, $field_info);

    // Load asset base field storage definitions.
    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('asset');

    // Load test asset bundle field definitions.
    $field_definitions = $this->entityFieldManager->getFieldDefinitions('asset', 'test');

    // Confirm that all field definitions were created.
    $field_names = array_keys($field_info);
    foreach ($field_names as $field_name) {
      $this->assertArrayHasKey($field_name, $field_storage_definitions);
      $this->assertArrayHasKey($field_name, $field_definitions);
    }

    // Test field label.
    $this->assertEquals($field_info['test_string']['label'], $field_storage_definitions['test_string']->getLabel());
    $this->assertEquals($field_info['test_string']['label'], $field_definitions['test_string']->getLabel());

    // Test field description.
    $this->assertEquals($field_info['test_string']['description'], $field_storage_definitions['test_string']->getDescription());
    $this->assertEquals($field_info['test_string']['description'], $field_definitions['test_string']->getDescription());

    // Test computed field.
    // @todo

    // Test required field.
    $this->assertEquals(TRUE, $field_definitions['test_string']->isRequired());

    // Test revisionable field.
    // @todo

    // Test multi-value field.
    $this->assertEquals(-1, $field_storage_definitions['test_string']->getCardinality());
    $this->assertEquals(-1, $field_definitions['test_string']->getCardinality());

    // Test field with specific cardinality.
    // @todo

    // Test translatable field.
    // @todo

    // Test field with default value callback.
    // @todo

    // Test form and view display settings...
    // @todo

    // Test boolean field.
    // @todo

    // Test decimal field.
    // @todo

    // Test entity_reference field.
    // @todo

    // Test entity_reference_revisions field.
    // @todo

    // Test file field.
    // @todo

    // Test image field.
    // @todo

    // Test fraction field.
    // @todo

    // Test geofield field.
    // @todo

    // Test id_tag field.
    // @todo

    // Test integer field.
    // @todo

    // Test inventory field.
    // @todo

    // Test list_string field.
    // @todo

    // Test string field.
    // @todo

    // Test string_long field.
    // @todo

    // Test text_long field.
    // @todo

    // Test timestamp field.
    // @todo
  }

}
