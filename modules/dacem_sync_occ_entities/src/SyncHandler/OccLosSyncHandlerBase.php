<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\SyncHandler;

use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a base sync handler for OCC Entities.
 */
class OccLosSyncHandlerBase implements SyncHandlerInterface {

  public const SYNC_HANDLER_ID = 'los_sync_handler';

  public const TARGET_ENTITY_TYPE_ID = 'occ_los';
  public const TARGET_UNIQUE_PER_HEI = 'code';
  public const TARGET_HEI_FIELD = 'hei';

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return self::SYNC_HANDLER_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function onInsert(string $entity_type_id, string $bundle, string $uuid): void {
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void {
  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void {
  }

}
