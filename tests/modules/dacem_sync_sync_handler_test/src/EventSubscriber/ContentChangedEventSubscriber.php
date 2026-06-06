<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_sync_handler_test\EventSubscriber;

use Drupal\Core\Queue\QueueFactory;
use Drupal\dacem_sync\Event\ContentChangedEvent;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_sync_handler_test\SyncHandler\NeutralSyncHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for testing purposes.
 */
class ContentChangedEventSubscriber implements EventSubscriberInterface {

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory service.
   */
  public function __construct(
    QueueFactory $queue_factory,
  ) {
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ContentChangedEvent::EVENT_NAME => ['onContentChanged'],
    ];
  }

  /**
   * Subscribe to the content changed event dispatched.
   *
   * @param \Drupal\dacem_sync\Event\ContentChangedEvent $event
   *   The event object.
   */
  public function onContentChanged(ContentChangedEvent $event) {
    if (
      $event->entityTypeId === NeutralSyncHandler::SOURCE_ENTITY_TYPE_ID &&
      $event->bundle === NeutralSyncHandler::SOURCE_BUNDLE
    ) {
      $params = [
        'entity_type_id' => $event->entityTypeId,
        'bundle' => $event->bundle,
        'uuid' => $event->uuid,
        'operation' => $event->operation,
      ];

      $params['sync_handler'] = NeutralSyncHandler::SYNC_HANDLER_ID;

      $queue = $this->queueFactory->get(DacemSyncQueueWorker::QUEUE_NAME);
      $queue->createItem($params);
    }
  }

}
