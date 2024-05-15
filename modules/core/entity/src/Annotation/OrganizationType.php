<?php

namespace Drupal\farm_entity\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the organization type plugin annotation object.
 *
 * Plugin namespace: Plugin\Organization\OrganizationType.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class OrganizationType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The organization type label.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
