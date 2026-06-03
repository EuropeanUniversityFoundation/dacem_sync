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

  /**
   * Builds transformed data from a source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   The source entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   *
   * @return array
   *   The transformed data.
   */
  public function buildFromSource(EntityInterface $source, array $map): array {
    $data = [];

    foreach ($map as $field_name => $strategy) {
      $transformer_id = $strategy['transformer'];
      $transformer = $this->dataTransformerResolver->get($transformer_id);

      /** @var \Drupal\Core\Entity\ContentEntityInterface $source */
      $data[$field_name] = $transformer->transform($source, $strategy);
    }

    return $data;
  }

  /**
   * Extracts data from a target entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $target
   *   The target entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   *
   * @return array
   *   The extracted data.
   */
  public function extractFromTarget(EntityInterface $target, array $map): array {
    $data = [];

    foreach ($map as $field_name => $strategy) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
      $data[$field_name] = $target->get($field_name)->getValue();
    }

    return $data;
  }

  /**
   * Compares data arrays and returns the diff.
   *
   * @param array $source_data
   *   Data built from the source entity.
   * @param array $target_data
   *   Data extracted from the target entity.
   *
   * @return array
   *   The diff.
   */
  public function diff(array $source_data, array $target_data): array {
    $diff = [];

    foreach ($source_data as $field_name => $field_data) {
      if (serialize($field_data) !== serialize($target_data[$field_name])) {
        $diff[$field_name] = $field_data;
      }
    }

    foreach ($target_data as $field_name => $field_data) {
      if (!array_key_exists($field_name, $source_data)) {
        $diff[$field_name] = [];
      }
    }

    return $diff;
  }

}
