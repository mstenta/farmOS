<?php

declare(strict_types=1);

namespace Drupal\farm_api_test\Plugin\PlanRecord\PlanRecordType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\PlanRecordType;
use Drupal\farm_entity\Plugin\PlanRecord\PlanRecordType\FarmPlanRecordType;

/**
 * Provides the test plan record type.
 */
#[PlanRecordType(
  id: 'test',
  label: new TranslatableMarkup('Test'),
)]
class TestPlanRecord extends FarmPlanRecordType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = parent::buildFieldDefinitions();
    $fields['label'] = $this->farmFieldFactory->bundleFieldDefinition([
      'type' => 'string',
      'label' => $this->t('Label'),
    ]);
    return $fields;
  }

}
