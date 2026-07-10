<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a ReferencedEntityField data transformer.
 */
class ReferencedEntityField implements DataTransformerInterface {

  public const GLUE = '.';

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'referenced_entity_field';
  }

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $source_chain = explode(self::GLUE, $strategy['source']);

    $reference = $source_chain[0];
    // $entity_type_id = $source_chain[1];
    $field_name = $source_chain[2];

    $reference_field = $source->get($reference);
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $reference_field */
    $referenced_entities = $reference_field->referencedEntities();

    foreach ($referenced_entities as $entity) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $source_field_data = $entity->get($field_name)->getValue();
      foreach ($source_field_data as $item) {
        $transformed = [];
        foreach ($strategy['properties'] as $target_prop => $source_prop) {
          if (array_key_exists($source_prop, $item)) {
            $transformed[$target_prop] = $item[$source_prop];
          }
        }

        $output[] = $transformed;
      }
    }

    return $output;
  }

}
