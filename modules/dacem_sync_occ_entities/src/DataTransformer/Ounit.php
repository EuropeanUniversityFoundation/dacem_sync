<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformer\DataTransformerBase;
use Drupal\dacem_sync\EntityManager;

/**
 * Defines an OUnit data transformer.
 */
class Ounit extends DataTransformerBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\dacem_sync\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs data transformer.
   *
   * @param \Drupal\dacem_sync\EntityManager $entity_manager
   *   The entity manager.
   */
  public function __construct(
    EntityManager $entity_manager,
  ) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'ounit';
  }

  /**
   * {@inheritdoc}
   */
  public function doTransform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $reference = $strategy['source'];
    $reference_field = $source->get($reference);
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $reference_field */
    $referenced_entities = $reference_field->referencedEntities();

    foreach ($referenced_entities as $entity) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      if ($this->ounitSyncHandlerExists()) {
        $source_uuid = $entity->uuid();
        $ounit = $this->entityManager->loadBySourceUuid('ounit', $source_uuid);
        if ($ounit) {
          $output[] = ['target_id' => (string) $ounit->id()];
        }
      }
    }

    return $output;
  }

  /**
   * Helper method to detect whether 'dacem_sync_ewp_ounits' is enabled.
   */
  protected function ounitSyncHandlerExists(): bool {
    $class = 'Drupal\\dacem_sync_ewp_ounits\\SyncHandler\\OunitSyncHandler';
    return class_exists($class);
  }

}
