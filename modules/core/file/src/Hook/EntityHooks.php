<?php

declare(strict_types=1);

namespace Drupal\farm_file\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_file\Access\FileAccessControlHandler;

/**
 * Entity hook implementations for farm_file.
 */
class EntityHooks {

  /**
   * Implements hook_entity_type_alter().
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types) {
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_types */

    // Only modify the file entity type.
    if (!isset($entity_types['file'])) {
      return;
    }

    // Extend the file entity access handler.
    $entity_types['file']->setHandlerClass('access', FileAccessControlHandler::class);

    // Set the file entity collection permission.
    $entity_types['file']->set('collection_permission', 'access file collection');
  }

}
