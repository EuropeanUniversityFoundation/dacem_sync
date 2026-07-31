<?php

namespace Drupal\dacem_sync\Drush\Commands;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to queue legacy content for syncing.
 */
final class DacemSyncCommands extends DrushCommands implements ContainerInjectionInterface {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = new static();

    $instance->database = $container->get('database');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->queueFactory = $container->get('queue');
    $instance->syncHandlerResolver = $container->get('dacem_sync.sync_handler_resolver');

    return $instance;
  }

  /**
   * Queues legacy entities for syncing.
   */
  #[CLI\Command(name: 'dacem-sync:queue')]
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

    $source_table = $source_definition->getBaseTable();
    $source_data_table = $source_definition->getDataTable();

    $source_entity_keys = $source_definition->getKeys();
    $source_id_key = $source_entity_keys['id'];
    $source_uuid_key = $source_entity_keys['uuid'];
    $source_bundle_key = $source_entity_keys['bundle'];

    $target_definition = $this->entityTypeManager
      ->getDefinition($target_entity_type_id);

    $target_table = $target_definition->getBaseTable();

    $target_base_field = EntityManager::BASE_FIELD;

    $query = $this->database->select($source_table, 'source');

    if ($source_data_table) {
      $query->innerJoin(
        $source_data_table,
        'source_data',
        "source_data.$source_id_key = source.$source_id_key"
      );

      $source_bundle_alias = 'source_data';
    }
    else {
      $source_bundle_alias = 'source';
    }

    $query->leftJoin(
      $target_table,
      'target',
      "target.$target_base_field = source.$source_uuid_key"
    );

    $query->fields('source', [$source_uuid_key]);

    $query->condition(
      "$source_bundle_alias.$source_bundle_key",
      $source_bundle
    );

    $query->isNull("target.$target_base_field");

    $legacy = $query->execute()->fetchCol();

    return $legacy;
  }

}
