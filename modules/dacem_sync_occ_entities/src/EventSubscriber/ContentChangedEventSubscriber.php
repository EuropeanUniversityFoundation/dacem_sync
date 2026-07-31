<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dacem_sync\Event\ContentChangedEvent;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_occ_entities\SyncHandler\CourseSyncHandler;
use Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DACEM Sync OCC Entities content changed event subscriber.
 */
class ContentChangedEventSubscriber implements EventSubscriberInterface {

  public const HANDLER_MAP = [
    CourseSyncHandler::SYNC_HANDLER_ID => [
      'entity_type_id' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
    ],
    ProgrammeSyncHandler::SYNC_HANDLER_ID => [
      'entity_type_id' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
    ],
  ];

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    QueueFactory $queue_factory,
  ) {
    $this->logger = $logger_factory->get('dacem_sync_occ_entities');
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
    foreach (self::HANDLER_MAP as $handler_id => $map) {
      if (
        $event->entityTypeId === $map['entity_type_id'] &&
        $event->bundle === $map['bundle']
      ) {
        $params = [
          'entity_type_id' => $event->entityTypeId,
          'bundle' => $event->bundle,
          'uuid' => $event->uuid,
          'operation' => $event->operation,
        ];

        // $message = implode(':', array_values($params));

        // $this->logger->notice($message);

        $params['sync_handler'] = $handler_id;

        $queue = $this->queueFactory->get(DacemSyncQueueWorker::QUEUE_NAME);
        $queue->createItem($params);
      }
    }
  }

}
