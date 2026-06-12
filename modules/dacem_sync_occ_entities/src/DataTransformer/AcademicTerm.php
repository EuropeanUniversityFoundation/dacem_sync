<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a AcademicTerm data transformer.
 */
class AcademicTerm implements DataTransformerInterface {

  public const GLUE = '.';
  public const SEPARATOR = '/';

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'academic_term';
  }

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $reference_field_chain = explode(self::GLUE, $strategy['source'][1]);

    $reference = $reference_field_chain[0];
    $entity_type_id = $reference_field_chain[1];
    $referenced_field_name = $reference_field_chain[2];

    $reference_field = $source->get($reference);
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $reference_field */
    $referenced_entities = $reference_field->referencedEntities();
    $referenced_entity = reset($referenced_entities);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $referenced_entity */
    $referenced_field_data = $referenced_entity
      ->get($referenced_field_name)
      ->getValue();

    $referenced_field_value = reset($referenced_field_data);
    $denominator = (string) array_values($referenced_field_value)[0];

    $source_field_name = $strategy['source'][0];
    $source_field_data = $source->get($source_field_name)->getValue();

    foreach ($source_field_data as $item) {
      $transformed = [];
      foreach ($strategy['properties'] as $target_prop => $source_prop) {
        if (array_key_exists($source_prop, $item)) {
          $numerator = $item[$source_prop];
          $fraction = implode(self::SEPARATOR, [$numerator, $denominator]);
          $transformed[$target_prop] = $fraction;
        }
      }

      $output[] = $transformed;
    }

    return $output;
  }

}
