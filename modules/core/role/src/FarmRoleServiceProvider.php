<?php

namespace Drupal\farm_role;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Override the permission_checker service with our own class.
 */
class FarmRoleServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('permission_checker');
    $definition->addArgument(new Reference('entity_type.manager'));
    $definition->addArgument(new Reference('plugin.manager.managed_role_permissions'));
    $definition->setClass('Drupal\farm_role\ManagedRolePermissionChecker');
  }

}
