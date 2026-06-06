<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_sync_handler_test\SyncHandler;

use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a neutral sync handler for testing purposes.
 */
class NeutralSyncHandler implements SyncHandlerInterface {

  public const SYNC_HANDLER_ID = 'neutral';

  public const SOURCE_ENTITY_TYPE_ID = 'node';
  public const SOURCE_BUNDLE = 'example';

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
