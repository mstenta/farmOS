<?php

namespace Drupal\Tests\farm_quick\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests for farmOS quick forms.
 *
 * @group farm
 */
class QuickFormTest extends KernelTestBase {

  /**
   * The quick form instance manager.
   *
   * @var \Drupal\farm_quick\QuickFormInstanceManagerInterface
   */
  protected $quickFormInstanceManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset',
    'entity_reference_revisions',
    'farm_field',
    'farm_log_quantity',
    'farm_quick',
    'farm_quick_test',
    'farm_unit',
    'fraction',
    'log',
    'options',
    'quantity',
    'state_machine',
    'taxonomy',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->quickFormInstanceManager = \Drupal::service('quick_form.instance_manager');
    $this->installEntitySchema('asset');
    $this->installEntitySchema('log');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('quantity');
    $this->installEntitySchema('user');
    $this->installConfig([
      'farm_quick_test',
    ]);
  }

  /**
   * Test quick form discovery.
   */
  public function testQuickFormDiscovery() {

    // Load quick forms.
    /** @var \Drupal\farm_quick\Entity\QuickFormInstanceInterface[] $quick_forms */
    $quick_forms = $this->quickFormInstanceManager->getInstances();

    // Confirm that three quick forms were discovered.
    $this->assertEquals(3, count($quick_forms));

    // Confirm the label, description, helpText, and permissions of the test
    // quick form.
    $this->assertEquals('Test quick form', $quick_forms['test']->getLabel());
    $this->assertEquals('Test quick form description.', $quick_forms['test']->getDescription());
    $this->assertEquals('Test quick form help text.', $quick_forms['test']->getHelpText());
    $this->assertEquals(['create test log'], $quick_forms['test']->getPlugin()->getPermissions());

    // Confirm the label, description, helpText, and permissions of the
    // configurable_test quick form.
    $this->assertEquals('Test configurable quick form', $quick_forms['configurable_test']->getLabel());
    $this->assertEquals('Test configurable quick form description.', $quick_forms['configurable_test']->getDescription());
    $this->assertEquals('Test configurable quick form help text.', $quick_forms['configurable_test']->getHelpText());
    $this->assertEquals(['create test log'], $quick_forms['configurable_test']->getPlugin()->getPermissions());

    // Confirm default configuration.
    $this->assertEquals(['test_default' => 100], $quick_forms['configurable_test']->getPlugin()->defaultConfiguration());

    // Confirm overridden label, description, and helpText of the
    // configurable_test2 quick form.
    $this->assertEquals('Test configurable quick form 2', $quick_forms['configurable_test2']->getLabel());
    $this->assertEquals('Overridden description', $quick_forms['configurable_test2']->getDescription());
    $this->assertEquals('Overridden help text', $quick_forms['configurable_test2']->getHelpText());

    // Confirm configuration of configurable_test2 quick form.
    $this->assertEquals(['test_default' => 500], $quick_forms['configurable_test2']->getPlugin()->defaultConfiguration());
  }

  /**
   * Test quick form submission.
   */
  public function testQuickFormSubmission() {

    // Programmatically submit the test quick form.
    $form_state = (new FormState())->setValues([
      'count' => '12',
    ]);
    \Drupal::formBuilder()->submitForm('\Drupal\farm_quick\Form\QuickForm', $form_state, 'test');

    // Load the form state storage.
    $storage = $form_state->getStorage();

    // Confirm that an asset was created.
    $this->assertNotEmpty($storage['assets'][0]->id());

    // Confirm that the asset is linked to the quick form.
    $this->assertEquals('test', $storage['assets'][0]->quick[0]);

    // Confirm that a log was created.
    $this->assertNotEmpty($storage['logs'][0]->id());

    // Confirm that the log is linked to the quick form.
    $this->assertEquals('test', $storage['logs'][0]->quick[0]);

    // Confirm that the log's quantity type is test.
    $this->assertEquals('test', $storage['logs'][0]->get('quantity')->referencedEntities()[0]->bundle());

    // Confirm that a quantity was created and its type is test2.
    $this->assertNotEmpty($storage['quantities'][0]->id());
    $this->assertEquals('test2', $storage['quantities'][0]->bundle());

    // Confirm that three terms were created or loaded.
    $this->assertEquals(3, count($storage['terms']));
    foreach ($storage['terms'] as $term) {
      $this->assertNotEmpty($term->id());
    }

    // Confirm that the second and third terms have the same ID.
    $match = $storage['terms'][1]->id() == $storage['terms'][2]->id();
    $this->assertTrue($match);
  }

}
