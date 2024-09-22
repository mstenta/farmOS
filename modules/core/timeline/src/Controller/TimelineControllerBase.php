<?php

namespace Drupal\farm_timeline\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\farm_log\AssetLogsInterface;
use Drupal\log\Entity\LogInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Base controller for timeline data endpoints.
 */
abstract class TimelineControllerBase extends ControllerBase {

  use AutowireTrait;

  public function __construct(
    protected AssetLogsInterface $assetLogs,
    protected UuidInterface $uuidService,
    protected TypedDataManagerInterface $typedDataManager,
    #[Autowire(service: 'serializer')]
    protected SerializerInterface $serializer,
  ) {}

  /**
   * Helper function for building a single log task.
   *
   * @param \Drupal\log\Entity\LogInterface $log
   *   The log entity.
   *
   * @return array
   *   Returns an array representing a single log task.
   */
  protected function buildLogTask(LogInterface $log) {
    return [
      'id' => $this->uuidService->generate(),
      'link_url' => $log->toUrl()->toString(),
      'start' => $log->get('timestamp')->value,
      'end' => $log->get('timestamp')->value + 86400,
      'meta' => [
        'label' => $log->label(),
        'entity_id' => $log->id(),
        'entity_type' => 'log',
        'entity_bundle' => $log->bundle(),
        'log_status' => $log->get('status')->value,
      ],
      'classes' => [
        'log',
        'log--' . $log->bundle(),
        'log--status-' . $log->get('status')->value,
      ],
    ];
  }

}
