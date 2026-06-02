<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Builds entities from mapped and transformed data.
 */
class SyncEntityBuilder {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs entity manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('dacem_sync');
  }

  /**
   * Creates a target entity from a source entity.
   *
   * @param string $entity_type_id
   *   The target entity type ID.
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   */
  public function createTargetFromSource(string $entity_type_id, EntityInterface $source, array $map): void {
  }

  /**
   * Updates a target entity from a source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $target
   *   The target entity.
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   */
  public function updateTargetFromSource(EntityInterface $target, EntityInterface $source, array $map): void {
  }

}
