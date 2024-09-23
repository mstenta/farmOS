<?php

namespace Drupal\asset\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\asset\Entity\AssetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action that clones an asset.
 *
 * @Action(
 *   id = "asset_clone_action",
 *   label = @Translation("Clone an asset"),
 *   type = "asset"
 * )
 */
class AssetClone extends EntityActionBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs an AssetClone object.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
