<?php

namespace Drupal\dacem_sync\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when a content item changes.
 */
class ContentChangedEvent extends Event {

  const EVENT_NAME = 'dacem_sync_content_changed';

  /**
   * The entity type ID.
   *
   * @var string
   */
  public $entityTypeId;

  /**
   * The entity ID.
   *
   * @var int
   */
  public $id;

  /**
   * The operation.
   *
   * @var string
   */
  public $operation;

  /**
   * Constructs the object.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param int $id
   *   The entity ID.
   * @param string $operation
   *   The operation.
   */
  public function __construct(string $entity_type_id, int $id, string $operation) {
    $this->entityTypeId = $entity_type_id;
    $this->id = $id;
    $this->operation = $operation;
  }

}
