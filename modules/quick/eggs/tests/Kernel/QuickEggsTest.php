<?php

namespace Drupal\Tests\farm_quick_eggs\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\farm_quick\Kernel\QuickFormTestBase;

/**
 * Tests for farmOS egg harvest quick form.
 *
 * @group farm
 */
class QuickEggsTest extends QuickFormTestBase {

  /**
   * Quick form ID.
   *
   * @var string
   */
  protected $quickFormId = 'eggs';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_harvest',
    'farm_quantity_standard',
    'farm_quick_eggs',
    'farm_unit',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig([
      'farm_harvest',
      'farm_quantity_standard',
    ]);
  }

  /**
   * Test simple planting quick form submission.
   */
  public function testQuickEggs() {

    // Get today's date.
    $today = new DrupalDateTime('midnight');

    // Programmatically submit the egg harvest quick form.
    $this->submitQuickForm([
      'date' => [
        'date' => $today->format('Y-m-d'),
        'time' => $today->format('H:i:s'),
      ],
      'quantity' => 12,
    ]);

    // Load logs.
    $logs = $this->logStorage->loadMultiple();

    // Confirm that one log exists.
    $this->assertCount(1, $logs);

    // Check that the harvest log's fields were populated correctly.
    $log = $logs[1];
    $this->assertEquals('harvest', $log->bundle());
    $this->assertEquals($today->getTimestamp(), $log->get('timestamp')->value);
    $this->assertEquals('Collected 12 egg(s)', $log->label());
    $this->assertEquals('count', $log->get('quantity')->referencedEntities()[0]->get('measure')->value);
    $this->assertEquals('12', $log->get('quantity')->referencedEntities()[0]->get('value')[0]->get('decimal')->getValue());
    $this->assertEquals('egg(s)', $log->get('quantity')->referencedEntities()[0]->get('units')->referencedEntities()[0]->get('name')->value);
    $this->assertEquals('done', $log->get('status')->value);
  }

}
