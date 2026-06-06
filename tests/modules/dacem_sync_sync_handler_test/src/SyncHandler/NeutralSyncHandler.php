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
    $this->inserted[] = [$entity_type_id, $bundle, $uuid];
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void {
    $this->updated[] = [$entity_type_id, $bundle, $uuid];
  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void {
    $this->deleted[] = [$entity_type_id, $bundle, $uuid];
  }

}
