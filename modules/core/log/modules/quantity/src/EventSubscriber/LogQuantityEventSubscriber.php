<?php

namespace Drupal\farm_log_quantity\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\log\Event\LogEvent;
use Drupal\quantity\Event\QuantityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to events related to log quantities.
 */
class LogQuantityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      LogEvent::CLONE => 'logClone',
      LogEvent::DELETE => 'logDelete',
      QuantityEvent::DELETE => 'quantityDelete',
    ];
  }

  /**
   * Perform actions on log clone.
   *
   * @param \Drupal\log\Event\LogEvent $event
   *   The log event.
   */
  public function logClone(LogEvent $event) {

    // Get the log entity from the event.
    $log = $event->log;

    // Bail if the log does not reference any quantities.
    if ($log->get('quantity')->isEmpty()) {
      return;
    }

    // Duplicate each referenced quantity.
    $new_quantities = [];
    /** @var \Drupal\quantity\Entity\QuantityInterface $quantity */
    foreach ($log->get('quantity')->referencedEntities() as $quantity) {
      $duplicate_quantity = $quantity->createDuplicate();
      $new_quantities[] = $duplicate_quantity;
    }

    // Update the log to reference the new duplicated quantities.
    $log->set('quantity', $new_quantities);
  }

  /**
   * Perform actions on log delete.
   *
   * @param \Drupal\log\Event\LogEvent $event
   *   The log event.
   */
  public function logDelete(LogEvent $event) {

    // Get the log entity from the event.
    $log = $event->log;

    // If the log doesn't have a quantity field, bail.
    if (!$log->hasField('quantity')) {
      return;
    }

    // Get any quantities the log references.
    $quantities = $log->quantity->referencedEntities();

    // Delete quantity entities.
    if (!empty($quantities)) {
      $this->entityTypeManager->getStorage('quantity')->delete($quantities);
    }
  }

  /**
   * Perform actions on quantity delete.
   *
   * @param \Drupal\quantity\Event\QuantityEvent $event
   *   The quantity event.
   */
  public function quantityDelete(QuantityEvent $event) {

    // Get the quantity entity from the event.
    $quantity = $event->quantity;

    // Look up logs that reference the quantity.
    $log_storage = $this->entityTypeManager->getStorage('log');
    $query = $log_storage->getQuery();
    $query->condition('quantity.target_id', $quantity->id());
    $query->accessCheck(FALSE);
    $log_ids = $query->execute();
    /** @var \Drupal\log\Entity\LogInterface[] $logs */
    $logs = [];
    if (!empty($log_ids)) {
      $logs = $log_storage->loadMultiple($log_ids);
    }

    // Remove references to the quantity from the log and save a revision.
    foreach ($logs as $log) {
      $log->set('quantity', array_filter($log->get('quantity')->getValue(), function ($value) use ($quantity) {
        if (!empty($value['target_id']) && $value['target_id'] == $quantity->id()) {
          return FALSE;
        }
        return TRUE;
      }));
      $log->setNewRevision(TRUE);
      $log->setRevisionLogMessage($this->t('Removed reference to deleted quantity %uuid.', ['%uuid' => $quantity->uuid()]));
      $log->save();
    }
  }

}
