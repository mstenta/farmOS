<?php

/**
 * @file
 * Post update hooks for the quantity module.
 */

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\system\Entity\Action;

/**
 * Create plain text view mode for quantities.
 */
function quantity_post_update_plain_text_view_mode(&$sandbox) {
  $view_mode = EntityViewMode::create([
    'id' => 'quantity.plain_text',
    'label' => 'Plain text',
    'targetEntityType' => 'quantity',
    'cache' => FALSE,
    'dependencies' => [
      'enforced' => [
        'module' => [
          'quantity',
        ],
      ],
      'module' => [
        'quantity',
      ],
    ],
  ]);
  $view_mode->save();
}

/**
 * Create quantity delete action.
 */
function quantity_post_update_delete_action(&$sandbox) {
  $action = Action::create([
    'id' => 'quantity_delete_action',
    'label' => t('Delete quantity'),
    'type' => 'quantity',
    'plugin' => 'entity:delete_action:quantity',
    'configuration' => [],
    'dependencies' => [
      'module' => [
        'quantity',
      ],
    ],
  ]);
  $action->save();
}
