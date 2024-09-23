<?php

namespace Drupal\farm_log;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\asset\Entity\AssetInterface;

/**
 * Service for loading logs that reference assets.
 */
class AssetLogs implements AssetLogsInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Log query factory.
   *
   * @var \Drupal\farm_log\LogQueryFactoryInterface
   */
  protected LogQueryFactoryInterface $logQueryFactory;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\farm_log\LogQueryFactoryInterface $log_query_factory
   *   Log query factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LogQueryFactoryInterface $log_query_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logQueryFactory = $log_query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getLogs(AssetInterface $asset, ?string $log_type = NULL, bool $access_check = TRUE): array {
    $log_ids = $this->query($asset, $log_type, $access_check)->execute();
    if (empty($log_ids)) {
      return [];
    }
    return $this->entityTypeManager->getStorage('log')->loadMultiple($log_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstLog(AssetInterface $asset, ?string $log_type = NULL, bool $access_check = TRUE) {
    $log_ids = $this->query($asset, $log_type, $access_check, 1)->execute();
    if (empty($log_ids)) {
      return NULL;
    }
    return $this->entityTypeManager->getStorage('log')->load(reset($log_ids));
  }

  /**
   * Build a log query.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   * @param string|null $log_type
   *   Optionally filter by log type.
   * @param bool $access_check
   *   Whether to check log entity access.
   * @param int|null $limit
   *   The number of logs to return.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A query object.
   */
  protected function query(AssetInterface $asset, ?string $log_type = NULL, bool $access_check = TRUE, ?int $limit = NULL) {
    $options = [
      'asset' => $asset,
      'direction' => 'ASC',
    ];
    if (!empty($limit)) {
      $options['limit'] = $limit;
    }
    $query = $this->logQueryFactory->getQuery($options);
    if (!empty($log_type)) {
      $query->condition('type', $log_type);
    }
    $query->accessCheck($access_check);
    return $query;
  }

}
