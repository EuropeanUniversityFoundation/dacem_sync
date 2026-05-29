<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dacem_sync\Event\ContentChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DACEM Sync EWP OUnits content changed event subscriber.
 */
class ContentChangedEventSubscriber implements EventSubscriberInterface {

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
    $this->logger = $logger_factory->get('dacem_sync_ewp_ounits');
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
    if ($event->entityTypeId === 'node' && $event->bundle === 'organizational_unit') {
      $params = [
        'entity_type_id' => $event->entityTypeId,
        'bundle' => $event->bundle,
        'uuid' => $event->uuid,
        'operation' => $event->operation,
      ];

      $message = implode(':', array_values($params));

      $this->logger->notice($message);

      $params['sync_handler'] = 'ounit_sync_handler';

      $queue = $this->queueFactory->get('dacem_sync_queue_worker');
      $queue->createItem($params);
    }
  }

}
