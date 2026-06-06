<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Manages entity operations for the DACEM Sync module.
 */
class EntityManager {

  public const BUNDLE_PLACEHOLDER = 'bundle_key';
  public const BASE_FIELD = 'source_uuid';
  public const GROUP_TYPE_ID = '';
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
   * Builds an entity from a set of properties.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param array $properties
   *   An array of properties and values.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The new entity.
   */
  public function buildFromProperties(string $entity_type_id, array $properties): ContentEntityInterface {
    if (array_key_exists(self::BUNDLE_PLACEHOLDER, $properties)) {
      $bundle_key = $this->entityTypeManager
        ->getDefinition($entity_type_id)->getKey('bundle');
      $properties[$bundle_key] = $properties[self::BUNDLE_PLACEHOLDER];
      unset($properties[self::BUNDLE_PLACEHOLDER]);
    }

    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $entity = $storage->create($properties);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    return $entity;
  }

  /**
   * Loads an entity by a set of properties.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param array $properties
   *   An array of properties and values.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity, or NULL if not found.
   */
  public function loadByProperties(string $entity_type_id, array $properties): ?ContentEntityInterface {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $entities = $storage->loadByProperties($properties);

    if ($entities) {
      $entity = reset($entities);

      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      return $entity;
    }

    return NULL;
  }

  /**
   * Loads an entity by its UUID.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $uuid
   *   The UUID of the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity, or NULL if not found.
   */
  public function loadByUuid(string $entity_type_id, string $uuid): ?ContentEntityInterface {
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
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity, or NULL if not found.
   */
  public function loadBySourceUuid(string $entity_type_id, string $uuid): ?ContentEntityInterface {
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
    $entity = $this->loadByUuid($entity_type_id, $uuid);

    if ($entity && method_exists($entity, 'getOwnerId')) {
      $uid = $entity->getOwnerId();

      return $uid !== NULL ? (int) $uid : NULL;
    }

    return NULL;
  }

  /**
   * Gets the "hei" entity ID associated with an entity's group.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The grouped entity.
   *
   * @return int|null
   *   The ID of the referenced 'hei' entity, or NULL if not found.
   */
  public function getGroupHeiId(ContentEntityInterface $entity) {
    /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface $group_relationship_storage */
    $group_relationship_storage = $this->entityTypeManager
      ->getStorage('group_relationship');

    $relationships = $group_relationship_storage->loadByEntity($entity);

    foreach ($relationships as $relationship) {
      $group = $relationship->getGroup();

      if (
        $group->bundle() === self::GROUP_TYPE_ID &&
        $group->hasField(self::GROUP_HEI_REF)
      ) {
        $target_id = $group->get(self::GROUP_HEI_REF)->target_id;
        return ($target_id) ? (int) $target_id : NULL;
      }
    }

    return NULL;
  }

}
