<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\user\EntityOwnerInterface;

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
   * Constructs entity builder.
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
   * @param string $bundle
   *   The target entity bundle.
   * @param \Drupal\Core\Entity\ContentEntityInterface $source
   *   The source entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   */
  public function createTargetFromSource(
    string $entity_type_id,
    string $bundle,
    ContentEntityInterface $source,
    array $map,
  ): void {
    $new_data = $this->buildFromSource($source, $map);

    if ($entity_type_id !== $bundle) {
      $new_data[EntityManager::BUNDLE_PLACEHOLDER] = ['target_id' => $bundle];
    }

    $target = $this->entityManager
      ->buildFromProperties($entity_type_id, $new_data);

    if (
      $target instanceof EntityOwnerInterface &&
      $source instanceof EntityOwnerInterface
    ) {
      $target->setOwnerId($source->getOwnerId());
    }

    $target->set(EntityManager::BASE_FIELD, $source->uuid());

    $target->save();
  }

  /**
   * Updates a target entity from a source entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $target
   *   The target entity.
   * @param \Drupal\Core\Entity\ContentEntityInterface $source
   *   The source entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   */
  public function updateTargetFromSource(
    ContentEntityInterface $target,
    ContentEntityInterface $source,
    array $map,
  ): void {
    $source_data = $this->buildFromSource($source, $map);
    $target_data = $this->extractFromTarget($target, $map);
    $diff = $this->diff($source_data, $target_data);

    if (!empty($diff)) {
      foreach ($diff as $field_name => $field_values) {
        $target->set($field_name, $field_values);
      }

      if ($target instanceof RevisionableInterface) {
        $target->setNewRevision(TRUE);
      }

      if ($target instanceof RevisionLogInterface) {
        $target->setRevisionLogMessage(
          sprintf('Synced from source at %s', time())
        );

        if ($source instanceof RevisionLogInterface) {
          $target->setRevisionUserId($source->getRevisionUserId());
        }
      }

      $target->save();
    }
  }

  /**
   * Builds transformed data from a source entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $source
   *   The source entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   *
   * @return array
   *   The transformed data.
   */
  public function buildFromSource(ContentEntityInterface $source, array $map): array {
    $data = [];

    foreach ($map as $field_name => $strategy) {
      $transformer_id = $strategy['transformer'];
      $transformer = $this->dataTransformerResolver->get($transformer_id);

      $data[$field_name] = $transformer->transform($source, $strategy);
    }

    return $data;
  }

  /**
   * Extracts data from a target entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $target
   *   The target entity.
   * @param array $map
   *   Field mapping for target fields obtained from source fields.
   *
   * @return array
   *   The extracted data.
   */
  public function extractFromTarget(ContentEntityInterface $target, array $map): array {
    $data = [];

    foreach ($map as $field_name => $strategy) {
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
