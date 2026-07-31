<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformer\DataTransformerBase;
use Drupal\dacem_sync\EntityManager;

/**
 * Defines a RelatedProgramme data transformer.
 */
class RelatedProgramme extends DataTransformerBase {

  public const MANDATORY_KEYS = ['core', 'mandatory'];

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
    return 'related_programme';
  }

  /**
   * {@inheritdoc}
   */
  public function doTransform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $reference = $strategy['source'][0];
    $reference_field = $source->get($reference);
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $reference_field */
    $referenced_entities = $reference_field->referencedEntities();

    $type_field = $strategy['source'][1];
    $type_values = $source->get($type_field)->getValue();
    $type = $type_values[0]['value'] ?? NULL;

    $year_field = $strategy['source'][2];
    $year_values = $source->get($year_field)->getValue();
    $year = $year_values[0]['value'] ?? 0;

    $term_count_field_chain = explode(self::GLUE, $strategy['source'][3]);
    $term_count_field = $term_count_field_chain[2];

    $terms_per_year_field_chain = explode(self::GLUE, $strategy['source'][4]);
    $terms_per_year_field = $terms_per_year_field_chain[2];

    foreach ($referenced_entities as $entity) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $source_uuid = $entity->uuid();
      $los = $this->entityManager->loadBySourceUuid('occ_los', $source_uuid);

      $term_count_values = $entity->get($term_count_field)->getValue();
      $term_count = $term_count_values[0]['value'];

      $terms_per_year_values = $entity->get($terms_per_year_field)->getValue();
      $terms_per_year = $terms_per_year_values[0]['value'];

      if ($los && $term_count && $terms_per_year) {
        $year_count = (int) ceil($term_count / $terms_per_year);

        $output[] = [
          'target_id' => (string) $los->id(),
          'mandatory' => (string) (int) (in_array($type, self::MANDATORY_KEYS)),
          'year' => implode(self::SEPARATOR, [$year, $year_count]),
        ];
      }
    }

    return $output;
  }

}
