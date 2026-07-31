<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dacem_sync\Exception\MissingRequiredFieldException;
use Drupal\dacem_sync\SyncHandlerResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'dacem_sync_queue_worker' queue worker.
 */
#[QueueWorker(
  id: self::PLUGIN_ID,
  title: new TranslatableMarkup('DACEM Sync'),
  cron: ['time' => 60]
)]
class DacemSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public const PLUGIN_ID = 'dacem_sync_queue_worker';
  public const QUEUE_NAME = self::PLUGIN_ID;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The sync handler resolver.
   *
   * @var \Drupal\dacem_sync\SyncHandlerResolver
   */
  protected $syncHandlerResolver;

  /**
   * Constructs a new DacemSyncQueueWorker instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\dacem_sync\SyncHandlerResolver $sync_handler_resolver
   *   The sync handler resolver.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerChannelFactoryInterface $logger_factory,
    SyncHandlerResolver $sync_handler_resolver,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('dacem_sync');
    $this->syncHandlerResolver = $sync_handler_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('dacem_sync.sync_handler_resolver'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    $message = implode(':', array_values($data));

    $this->logger->notice($message);

    $entity_type_id = $data['entity_type_id'];
    $bundle = $data['bundle'];
    $uuid = $data['uuid'];

    $operation = $data['operation'];

    $sync_handler_id = $data['sync_handler'];
    $sync_handler = $this->syncHandlerResolver->get($sync_handler_id);

    try {
      switch ($operation) {
        case 'insert':
          $sync_handler->onInsert($entity_type_id, $bundle, $uuid);
          break;

        case 'update':
          $sync_handler->onUpdate($entity_type_id, $bundle, $uuid);
          break;

        case 'delete':
          $sync_handler->onDelete($entity_type_id, $bundle, $uuid);
          break;

        default:
          break;
      }
    }
    catch (MissingRequiredFieldException $e) {
      // Don't retry.
      $this->logger->warning($e->getMessage());
      return;
    }

  }

}
