<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_sync_handler_test\SyncHandler;

use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a neutral sync handler for testing purposes.
 */
class NeutralSyncHandler implements SyncHandlerInterface {

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
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void {
    // Do nothing.
  }

}
