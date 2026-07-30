<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

/**
 * Defines an interface for sync handlers.
 */
interface SyncHandlerInterface {

  /**
   * Returns the ID of the handler.
   */
  public function id(): string;

  /**
   * Returns the sync priority for the handler.
   */
  public function getSyncPriority(): int;

  /**
   * Returns the entity type ID of the source entity.
   */
  public function getSourceEntityTypeId(): string;

  /**
   * Returns the bundle of the source entity.
   */
  public function getSourceBundle(): string;

  /**
   * Returns the entity type ID of the target entity.
   */
  public function getTargetEntityTypeId(): string;

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
