<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\dacem_sync\Event\ContentChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DACEM Sync EWP OUnits content changed event subscriber.
 */
final class ContentChangedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    TranslationInterface $string_translation,
  ) {
    $this->logger            = $logger_factory->get('dacem_sync_ewp_ounits');
    $this->stringTranslation = $string_translation;
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
   * Subscribe to the user institution change event dispatched.
   *
   * @param \Drupal\dacem_sync\Event\ContentChangedEvent $event
   *   The event object.
   */
  public function onContentChanged(ContentChangedEvent $event) {
    $message = implode(':', [
      $event->entityTypeId,
      (string) $event->id,
      $event->operation,
    ]);

    $this->logger->notice($message);
  }

}
