<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\SyncHandler;

use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a sync handler for EWP OUnits.
 */
class OunitSyncHandler implements SyncHandlerInterface {

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
