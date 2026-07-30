<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_sync_handler_test\SyncHandler;

use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a neutral sync handler for testing purposes.
 */
class NeutralSyncHandler implements SyncHandlerInterface {

  public const SYNC_HANDLER_ID = 'neutral';
  public const SYNC_PRIORITY = 0;

  public const SOURCE_ENTITY_TYPE_ID = 'node';
  public const SOURCE_BUNDLE = 'example';

  public const TARGET_ENTITY_TYPE_ID = 'node';

  /**
   * Items inserted.
   */
  public array $inserted = [];

  /**
   * Items update.
   */
  public array $updated = [];

  /**
   * Items deleted.
   */
  public array $deleted = [];

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'neutral';
  }

  /**
   * {@inheritdoc}
   */
  public function getSyncPriority(): int {
    return self::SYNC_PRIORITY;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceEntityTypeId(): string {
    return self::SOURCE_ENTITY_TYPE_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceBundle(): string {
    return self::SOURCE_BUNDLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetEntityTypeId(): string {
    return self::TARGET_ENTITY_TYPE_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function onInsert(string $entity_type_id, string $bundle, string $uuid): void {
    $args = [
      'entity_type_id' => $entity_type_id,
      'bundle' => $bundle,
      'uuid' => $uuid,
    ];

    $this->inserted[] = $args;
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void {
    $args = [
      'entity_type_id' => $entity_type_id,
      'bundle' => $bundle,
      'uuid' => $uuid,
    ];

    $this->updated[] = $args;
  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void {
    $args = [
      'entity_type_id' => $entity_type_id,
      'bundle' => $bundle,
      'uuid' => $uuid,
    ];

    $this->deleted[] = $args;
  }

}
