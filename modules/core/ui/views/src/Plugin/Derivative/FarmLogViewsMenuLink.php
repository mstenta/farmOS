<?php

namespace Drupal\farm_ui_views\Plugin\Derivative;

/**
 * Provides menu links for farmOS Log Views.
 */
class FarmLogViewsMenuLink extends FarmViewsMenuLink {

  /**
   * {@inheritdoc}
   */
  protected string $entityType = 'log';

  /**
   * {@inheritdoc}
   */
  protected string $viewId = 'farm_log';

}
