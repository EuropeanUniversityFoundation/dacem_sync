<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\SyncHandler;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dacem_sync\FieldMappingInterface;
use Drupal\dacem_sync\EntityBuilder;
use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a sync handler for EWP OUnits.
 */
class OunitSyncHandler implements SyncHandlerInterface {

  public const SOURCE_ENTITY_TYPE_ID = 'node';
  public const SOURCE_BUNDLE = 'organizational_unit';
  public const SOURCE_UNIQUE_PER_HEI = 'field_ou_code';

  public const TARGET_ENTITY_TYPE_ID = 'ounit';
  public const TARGET_BUNDLE = self::TARGET_ENTITY_TYPE_ID;
  public const TARGET_UNIQUE_PER_HEI = 'ounit_code';
  public const TARGET_HEI_FIELD = 'parent_hei';

  /**
   * The entity builder.
   *
   * @var \Drupal\dacem_sync\EntityBuilder
   */
  protected $entityBuilder;

  /**
   * The entity manager.
   *
   * @var \Drupal\dacem_sync\EntityManager
   */
  protected $entityManager;

  /**
   * The field mapping.
   *
   * @var \Drupal\dacem_sync\FieldMappingInterface
   */
  protected $fieldMapping;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs sync handler.
   *
   * @param \Drupal\dacem_sync\EntityBuilder $entity_builder
   *   The entity type manager.
   * @param \Drupal\dacem_sync\EntityManager $entity_manager
   *   The entity type manager.
   * @param \Drupal\dacem_sync\FieldMappingInterface $field_mapping
   *   The field mapping.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    EntityBuilder $entity_builder,
    EntityManager $entity_manager,
    FieldMappingInterface $field_mapping,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->entityBuilder = $entity_builder;
    $this->entityManager = $entity_manager;
    $this->fieldMapping = $field_mapping;
    $this->logger = $logger_factory->get('dacem_sync_ewp_ounits');
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'ounit_sync_handler';
  }

  /**
   * {@inheritdoc}
   */
  public function onInsert(string $entity_type_id, string $bundle, string $uuid): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $source */
    $source = $this->entityManager->loadByUuid($entity_type_id, $uuid);

    // Check for existing target before creating new.
    // Source UUID is new, so compare by unique field combination.
    $source_hei_id = $this->entityManager->getGroupHeiId($source);
    $source_unique_per_hei = $source->get(self::SOURCE_UNIQUE_PER_HEI)->value;

    $target_properties = [
      self::TARGET_HEI_FIELD => $source_hei_id,
      self::TARGET_UNIQUE_PER_HEI => $source_unique_per_hei,
    ];
    $target = $this->entityManager
      ->loadByProperties(self::TARGET_ENTITY_TYPE_ID, $target_properties);

    $map = $this->fieldMapping
      ->mapping()[self::TARGET_ENTITY_TYPE_ID][self::TARGET_BUNDLE];

    if (!empty($target)) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
      $target->set(EntityManager::BASE_FIELD, $uuid);
      $this->entityBuilder->updateTargetFromSource($target, $source, $map);
    }
    else {
      $this->entityBuilder->createTargetFromSource(
        self::TARGET_ENTITY_TYPE_ID,
        self::TARGET_BUNDLE,
        $source,
        $map
      );
    }

  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(string $entity_type_id, string $bundle, string $uuid): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $source */
    $source = $this->entityManager->loadByUuid($entity_type_id, $uuid);

    // Check for missing target before attempting to update.
    $target = $this->entityManager
      ->loadBySourceUuid(self::TARGET_ENTITY_TYPE_ID, $uuid);

    $map = $this->fieldMapping
      ->mapping()[self::TARGET_ENTITY_TYPE_ID][self::TARGET_BUNDLE];

    if (empty($target)) {
      $this->entityBuilder->createTargetFromSource(
        self::TARGET_ENTITY_TYPE_ID,
        self::TARGET_BUNDLE,
        $source,
        $map
      );
    }
    else {
      $this->entityBuilder->updateTargetFromSource($target, $source, $map);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function onDelete(string $entity_type_id, string $bundle, string $uuid): void {
    $target = $this->entityManager
      ->loadBySourceUuid(self::TARGET_ENTITY_TYPE_ID, $uuid);
    if (!empty($target)) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
      $target->set('status', FALSE);
      $target->save();
    }

  }

}
