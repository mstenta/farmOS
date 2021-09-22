<?php

namespace Drupal\farm_group;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Override location and inventory services with our own classes.
 */
class FarmGroupServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    // Override the asset.location service class with our own.
    $definition = $container->getDefinition('asset.location');
    $definition->addArgument(new Reference('group.membership'));
    $definition->setClass('Drupal\farm_group\GroupAssetLocation');

    // Override the asset.inventory service class with our own.
    $definition = $container->getDefinition('asset.inventory');
    $definition->addArgument(new Reference('group.membership'));
    $definition->setClass('Drupal\farm_group\GroupAssetInventory');
  }

}
