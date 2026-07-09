<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\SyncHandler;

use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dacem_sync\FieldMappingInterface;
use Drupal\dacem_sync\EntityBuilder;
use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a sync handler for OCC Entities of type Course.
 */
class CourseSyncHandler extends OccLosSyncHandlerBase implements SyncHandlerInterface {

  public const SYNC_HANDLER_ID = 'course_sync_handler';

  public const SOURCE_ENTITY_TYPE_ID = 'node';
  public const SOURCE_BUNDLE = 'individual_educational_component';
  public const SOURCE_UNIQUE_PER_HEI = 'field_iec_code';

  public const TARGET_ENTITY_TYPE_ID = parent::TARGET_ENTITY_TYPE_ID;
  public const TARGET_BUNDLE = 'course';
  public const TARGET_UNIQUE_PER_HEI = parent::TARGET_UNIQUE_PER_HEI;
  public const TARGET_HEI_FIELD = parent::TARGET_HEI_FIELD;
  public const TARGET_OFF_SWITCH = 'course__deprecated';
  public const TARGET_OFF_STATE = TRUE;

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
    $this->logger = $logger_factory->get('dacem_sync_occ_entities');
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return self::SYNC_HANDLER_ID;
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
      $target->save();
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
    /** @var \Drupal\Core\Entity\ContentEntityInterface|null $source */
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
    elseif (empty($source)) {
      $this->onDelete($entity_type_id, $bundle, $uuid);
    }
    else {
      $this->entityBuilder->updateTargetFromSource($target, $source, $map);

      $target_state = (bool) $target->get(self::TARGET_OFF_SWITCH)->getString();

      if ((bool) $target_state == (bool) self::TARGET_OFF_STATE) {
        /** @var \Drupal\Core\Entity\ContentEntityInterface $target */
        // @phpstan-ignore booleanNot.alwaysFalse
        $target->set(self::TARGET_OFF_SWITCH, !self::TARGET_OFF_STATE);

        if ($target instanceof RevisionableInterface) {
          $target->setNewRevision(TRUE);
        }

        if ($target instanceof RevisionLogInterface) {
          $target->setRevisionLogMessage(
            sprintf('Republished at %s', time())
          );
        }

        $target->save();
      }
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
      $target->set(self::TARGET_OFF_SWITCH, self::TARGET_OFF_STATE);

      if ($target instanceof RevisionableInterface) {
        $target->setNewRevision(TRUE);
      }

      if ($target instanceof RevisionLogInterface) {
        $target->setRevisionLogMessage(
          sprintf('Source deleted at %s', time())
        );
      }

      $target->save();
    }

  }

}
