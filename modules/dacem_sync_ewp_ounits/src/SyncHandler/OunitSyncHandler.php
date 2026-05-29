<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\SyncHandler;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a sync handler for EWP OUnits.
 */
class OunitSyncHandler implements SyncHandlerInterface {

  public const SOURCE_ENTITY_TYPE_ID = 'node';
  public const SOURCE_BUNDLE = 'organizational_unit';
  public const SOURCE_UNIQUE_FIELD = 'uuid';

  public const TARGET_ENTITY_TYPE_ID = 'ounit';
  public const TARGET_BUNDLE = 'ounit';
  public const TARGET_UNIQUE_FIELD = 'ounit_id';

  public const BASE_FIELD = 'source_uuid';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('dacem_sync_ewp_ounits');
  }

  /**
   * {@inheritdoc}
   */
  public function onInsert(string $entity_type_id, string $bundle, string $uuid): void {
    // Load the source entity and extract the unique field value.
    $source_matches = $this->entityTypeManager
      ->getStorage($entity_type_id)
      ->loadByProperties(['uuid' => $uuid]);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $source */
    $source = reset($source_matches);

    $unique = $source->get(self::SOURCE_UNIQUE_FIELD)->value;

    // Check for existing target entity with the same unique value.
    $target_matches = $this->entityTypeManager
      ->getStorage(self::TARGET_ENTITY_TYPE_ID)
      ->loadByProperties([self::TARGET_UNIQUE_FIELD => $unique]);

    // If a target entity already exists, update its source_uuid.
    if (!empty($target_matches)) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
      $target = reset($target_matches);
      $target->set(self::BASE_FIELD, $source->uuid());
      $target->save();

      $this->updateFromSource($target, $source);
    }
    else {
      $this->createFromSource($source);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void {
    // Load the source entity.
    $source_matches = $this->entityTypeManager
      ->getStorage($entity_type_id)
      ->loadByProperties(['uuid' => $uuid]);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $source */
    $source = reset($source_matches);

    // Check for existing target entity with the same UUID.
    $target_matches = $this->entityTypeManager
      ->getStorage(self::TARGET_ENTITY_TYPE_ID)
      ->loadByProperties([self::BASE_FIELD => $uuid]);

    if (empty($target_matches)) {
      $this->createFromSource($source);
    }
    else {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
      $target = reset($target_matches);
      $this->updateFromSource($target, $source);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void {
    // Check for existing target entity with the same UUID.
    $target_matches = $this->entityTypeManager
      ->getStorage(self::TARGET_ENTITY_TYPE_ID)
      ->loadByProperties([self::BASE_FIELD => $uuid]);

    if (!empty($target_matches)) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
      $target = reset($target_matches);
      $target->set('status', FALSE);
      $target->save();
    }
  }

  /**
   * Creates a target entity from source entity data.
   */
  public function createFromSource(ContentEntityInterface $source): void {

  }

  /**
   * Updates a target entity from source entity data.
   */
  public function updateFromSource(ContentEntityInterface $target, ContentEntityInterface $source): void {

  }

}
