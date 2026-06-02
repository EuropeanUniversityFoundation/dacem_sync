<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Builds entities from mapped and transformed data.
 */
class EntityBuilder {

  /**
   * The data transformer resolver.
   *
   * @var \Drupal\dacem_sync\DataTransformerResolver
   */
  protected $dataTransformerResolver;

  /**
   * The entity manager.
   *
   * @var \Drupal\dacem_sync\EntityManager
   */
  protected $entityManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs entity manager.
   *
   * @param \Drupal\dacem_sync\DataTransformerResolver $data_transformer_resolver
   *   The data transformer resolver.
   * @param \Drupal\dacem_sync\EntityManager $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    DataTransformerResolver $data_transformer_resolver,
    EntityManager $entity_manager,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->dataTransformerResolver = $data_transformer_resolver;
    $this->entityManager = $entity_manager;
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
