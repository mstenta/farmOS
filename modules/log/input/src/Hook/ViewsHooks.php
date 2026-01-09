<?php

declare(strict_types=1);

namespace Drupal\farm_input\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Views hook implementations for farm_input.
 */
class ViewsHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_views_data().
   */
  // @todo should this be views_data_alter?
  #[Hook('views_data')]
  public function viewsData() {
    $data = [];

    // Add a quantity_material_type pseudo field to the log_field_data table.
    // This pseudo field only has a filter configured to support filtering logs
    // by the quantity material type.
    $data['log_field_data']['quantity_material_type'] = [
      'description' => $this->t('Filter by the material type of quantities referenced by this log.'),
      'entity_type' => 'quantity',
      'entity_field' => 'material_type',
      'filter' => [
        'title' => $this->t('Quantity material type'),
        'description' => $this->t('Filter by the material type of quantities referenced by this log.'),
        'id' => 'log_quantity_material_type',
        'field_name' => 'material_type',
      ],
    ];

    return $data;
  }

}
