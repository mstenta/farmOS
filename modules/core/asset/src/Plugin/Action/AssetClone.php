<?php

declare(strict_types=1);

namespace Drupal\asset\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\asset\Entity\AssetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action that clones an asset.
 */
#[Action(
  id: 'asset_clone_action',
  label: new TranslatableMarkup('Clone an asset'),
  type: 'asset',
)]
class AssetClone extends EntityActionBase {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    protected AccountInterface $currentUser,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @todo Use autowiring and remove this when the parent class does.
    // @see https://www.drupal.org/project/drupal/issues/3552110
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(?AssetInterface $asset = NULL) {
    if ($asset) {
      $cloned_asset = $asset->createDuplicate();
      $cloned_asset->setOwnerId($this->currentUser->id());
      $new_name = $asset->getName() . ' ' . $this->t('(clone of asset #@id)', ['@id' => $asset->id()]);
      $cloned_asset->setName($new_name);
      // @todo set revision log message.
      $cloned_asset->save();
      $this->messenger()->addMessage($this->t('Asset saved: <a href=":uri">%asset_label</a>', [':uri' => $cloned_asset->toUrl()->toString(), '%asset_label' => $cloned_asset->label()]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\asset\Entity\AssetInterface $object */
    $result = $object->access('view', $account, TRUE)
      ->andIf($object->access('create', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
