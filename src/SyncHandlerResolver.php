<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

/**
 * Resolves sync handlers based on tagged service ID.
 */
class SyncHandlerResolver {

  /**
   * Sync handlers discovered via service tags.
   *
   * @var array
   */
  protected $syncHandlers;

  /**
   * The constructor.
   *
   * @param iterable $sync_handlers
   *   The sync handlers.
   */
  public function __construct(iterable $sync_handlers) {
    foreach ($sync_handlers as $sync_handler) {
      $this->syncHandlers[$sync_handler->id()] = $sync_handler;
    }
  }

  /**
   * Returns a sync handler based on its ID.
   *
   * @param string $id
   *   The ID of the sync handler.
   *
   * @return \Drupal\dacem_sync\SyncHandlerInterface
   *   The sync handler matching the provided ID.
   */
  public function get(string $id): SyncHandlerInterface {
    if (!isset($this->syncHandlers[$id])) {
      throw new \InvalidArgumentException();
    }

    return $this->syncHandlers[$id];
  }

}
