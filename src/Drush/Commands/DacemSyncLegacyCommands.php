<?php

namespace Drupal\dacem_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync\SyncHandlerResolver;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Command\Command;

/**
 * Drush command to queue legacy content for syncing.
 */
final class DacemSyncLegacyCommands extends DrushCommands {

  public const DEFAULT_OPERATION = 'insert';

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The sync handler resolver.
   *
   * @var \Drupal\dacem_sync\SyncHandlerResolver
   */
  protected $syncHandlerResolver;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory service.
   * @param \Drupal\dacem_sync\SyncHandlerResolver $sync_handler_resolver
   *   The sync handler resolver.
   */
  public function __construct(
    Connection $database,
    EntityTypeManagerInterface $entity_type_manager,
    QueueFactory $queue_factory,
    SyncHandlerResolver $sync_handler_resolver,
  ) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->queueFactory = $queue_factory;
    $this->syncHandlerResolver = $sync_handler_resolver;
  }

  /**
   * Queues legacy entities for syncing.
   */
  #[CLI\Command(name: 'dacem-sync:legacy')]
  #[CLI\Option(
    name: 'dry-run',
    description: 'Report legacy entities without queueing them.'
  )]
  #[CLI\Help(description: 'Queues legacy entities for syncing.')]
  public function queue(bool $dry_run = FALSE): int {
    $this->io()->title("Legacy queue");

    $queue = $this->queueFactory->get(DacemSyncQueueWorker::QUEUE_NAME);

    $sync_sources = $this->getSortedSyncHandling();

    foreach ($sync_sources as $source) {
      $source_entity_type_id = $source['source_entity_type_id'];
      $source_bundle = $source['source_bundle'];
      $target_entity_type_id = $source['target_entity_type_id'];

      $this->io()->section("Legacy {$source_entity_type_id}:{$source_bundle}");

      $legacy_uuid = $this->getLegacy(
        $source_entity_type_id,
        $source_bundle,
        $target_entity_type_id,
      );

      $count = count($legacy_uuid);

      if (!$dry_run) {
        foreach ($legacy_uuid as $uuid) {
          $params = [
            'entity_type_id' => $source_entity_type_id,
            'bundle' => $source_bundle,
            'uuid' => $uuid,
            'operation' => self::DEFAULT_OPERATION,
            'sync_handler' => $source['sync_handler'],
          ];

          $queue->createItem($params);
        }

        $this->io()->success("Queued {$count} item(s).");
      }
      else {
        $this->io()->success("Found {$count} item(s) in dry run.");
      }
    }

    return Command::SUCCESS;
  }

  /**
   * Determines source entity types and bundles to query, sorted by priority.
   *
   * @return array
   *   Sorted list of combinations of entity type ID and bundle.
   */
  public function getSortedSyncHandling(): array {
    $sync = [];

    $handlers = $this->syncHandlerResolver->getHandlers();

    foreach ($handlers as $handler) {
      $sync[$handler->getSyncPriority()] = [
        'source_entity_type_id' => $handler->getSourceEntityTypeId(),
        'source_bundle' => $handler->getSourceBundle(),
        'target_entity_type_id' => $handler->getTargetEntityTypeId(),
        'sync_handler' => $handler->id(),
      ];
    }

    ksort($sync);
    return $sync;
  }

  /**
   * Retrieves UUIDs of legacy content without a sync counterpart.
   *
   * @param string $source_entity_type_id
   *   The entity type ID of the source entity.
   * @param string $source_bundle
   *   The entity bundle of the source entity.
   * @param string $target_entity_type_id
   *   The entity type ID of the target entity.
   *
   * @return array
   *   UUIDs of legacy entities matching the entity type ID and bundle.
   */
  public function getLegacy(string $source_entity_type_id, string $source_bundle, string $target_entity_type_id): array {
    $source_definition = $this->entityTypeManager
      ->getDefinition($source_entity_type_id);

    $source_table = $source_definition->getDataTable()
      ?: $source_definition->getBaseTable();

    $source_entity_keys = $source_definition->getKeys();
    $source_uuid_key = $source_entity_keys['uuid'];
    $source_bundle_key = $source_entity_keys['bundle'];

    $target_definition = $this->entityTypeManager
      ->getDefinition($target_entity_type_id);

    $target_table = $target_definition->getDataTable()
      ?: $target_definition->getBaseTable();

    $target_base_field = EntityManager::BASE_FIELD;

    $query = $this->database->select($source_table, 'source');

    $query->leftJoin(
      $target_table,
      'target',
      "target.$target_base_field = source.$source_uuid_key"
    );

    $query->fields('source', [$source_uuid_key]);

    $query->condition(
      "source.$source_bundle_key",
      $source_bundle
    );

    $query->isNull("target.$target_base_field");

    $legacy = $query->execute()->fetchCol();

    return $legacy;
  }

}
