<?php

declare(strict_types=1);

namespace Drupal\farm_action\Form;

use Drupal\Core\Form\ConfirmFormInterface;

/**
 * Provides a base confirmation form for entity actions.
 */
interface FarmActionConfirmFormInterface extends ConfirmFormInterface {

  /**
   * Perform action on entities after confirmation.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   An array of entity objects.
   */
  public function processEntities(array $entities): void;

}
