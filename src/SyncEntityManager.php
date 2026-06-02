<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Manages entity operations for the DACEM Sync module.
 */
class SyncEntityManager {

  public const BASE_FIELD = 'source_uuid';
  public const GROUP_HEI_REF = 'field_institution_profile';

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
   * Loads an entity by a set of properties.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param array $properties
   *   An array of properties and values.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity, or NULL if not found.
   */
  public function loadByProperties(string $entity_type_id, array $properties): ?EntityInterface {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    $entities = $storage->loadByProperties($properties);
    $entity = reset($entities);

    return ($entity) ? $entity : NULL;
  }

  /**
   * Loads an entity by its UUID.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $uuid
   *   The UUID of the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity, or NULL if not found.
   */
  public function loadByUuid(string $entity_type_id, string $uuid): ?EntityInterface {
    return $this->loadByProperties($entity_type_id, ['uuid' => $uuid]);
  }

  /**
   * Loads an entity by its source UUID.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $uuid
   *   The UUID of the source entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity, or NULL if not found.
   */
  public function loadBySourceUuid(string $entity_type_id, string $uuid): ?EntityInterface {
    return $this->loadByProperties($entity_type_id, [self::BASE_FIELD => $uuid]);
  }

  /**
   * Gets the owner user ID of an entity by its UUID.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $uuid
   *   The UUID of the entity.
   *
   * @return int|null
   *   The owner user ID, or NULL if not found/supported.
   */
  public function getOwnerIdByUuid(string $entity_type_id, string $uuid): ?int {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    $entities = $storage->loadByProperties(['uuid' => $uuid]);
    $entity = reset($entities);

    if ($entity && method_exists($entity, 'getOwnerId')) {
      $uid = $entity->getOwnerId();

      return $uid !== NULL ? (int) $uid : NULL;
    }

    return NULL;
  }

  /**
   * Gets the "hei" entity ID associated with a node's group.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity object.
   *
   * @return int|null
   *   The ID of the referenced 'hei' entity, or NULL if not found.
   */
  public function getGroupHeiId(NodeInterface $node) {
    /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $group_relationship_storage */
    $group_relationship_storage = $this->entityTypeManager
      ->getStorage('group_relationship');

    // Look up all group relationships referencing this specific node.
    $relationships = $group_relationship_storage->loadByEntity($node);

    foreach ($relationships as $relationship) {
      $group = $relationship->getGroup();

      return !$group->get(self::GROUP_HEI_REF)->isEmpty()
        ? (int) $group->get(self::GROUP_HEI_REF)->target_id
        : NULL;
    }

    return NULL;
  }

}
