<?php

namespace Drupal\farm_location;

use Drupal\asset\Entity\AssetInterface;

/**
 * Location index logic.
 */
interface LocationIndexInterface {

  /**
   * Reindex asset location history.
   */
  public function reindex(): void;

}
