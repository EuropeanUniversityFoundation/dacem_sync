<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

/**
 * Defines an interface for sync handlers.
 */
interface SyncHandlerInterface {

  /**
   * Handle entity insert operation.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The entity bundle.
   * @param string $uuid
   *   The entity UUID.
   */
  public function onInsert(string $entity_type_id, string $bundle, string $uuid): void;

  /**
   * Handle entity update operation.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The entity bundle.
   * @param string $uuid
   *   The entity UUID.
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void;

  /**
   * Handle entity delete operation.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The entity bundle.
   * @param string $uuid
   *   The entity UUID.
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void;

}
