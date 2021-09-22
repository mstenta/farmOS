<?php

namespace Drupal\farm_group;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\farm_inventory\AssetInventory;

/**
 * Group inventory logic.
 */
class GroupAssetInventory extends AssetInventory {

  /**
   * Group membership service.
   *
   * @var \Drupal\farm_group\GroupMembershipInterface
   */
  protected GroupMembershipInterface $groupMembership;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\farm_group\GroupMembershipInterface $group_membership
   *   Group membership service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TimeInterface $time, GroupMembershipInterface $group_membership) {
    parent::__construct($entity_type_manager, $time);
    $this->groupMembership = $group_membership;
  }

  /**
   * {@inheritdoc}
   */
  public function getInventory(AssetInterface $asset, string $measure = '', int $units = 0): array {

    // If the asset is new, it won't have inventory.
    if ($asset->isNew()) {
      return [];
    }

    // Get the inventory of this asset. If the asset is a group, this will also
    // recursively collect inventories from group member assets.
    $inventories = $this->getGroupMemberInventory($asset, $measure, $units);

    // Return the inventory summaries.
    return $inventories;
  }

  /**
   * Recursively collect inventories from group member assets.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The Asset entity.
   * @param string $measure
   *   The quantity measure of the inventory. See quantity_measures().
   * @param int $units
   *   The quantity units of the inventory (term ID).
   *
   * @return array
   *   Returns an array of asset inventory information.
   */
  protected function getGroupMemberInventory(AssetInterface $asset, string $measure = '', int $units = 0) {

    // First, get any inventories that are being directly tracked on this asset.
    $inventories = parent::getInventory($asset, $measure, $units);

    // If this is a group, recursively collect inventories from all members.
    if ($asset->bundle() == 'group') {
      $members = $this->groupMembership->getGroupMembers($asset);
      foreach ($members as $member) {
        $member_inventories = $this->getGroupMemberInventory($member, $measure, $units);
        $inventories = array_merge($inventories, $member_inventories);
      }
    }

    // Return the inventory summaries.
    return $inventories;
  }

}
