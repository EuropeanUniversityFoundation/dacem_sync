<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformer\DataTransformerBase;

/**
 * Defines a Negate data transformer.
 */
class Negate extends DataTransformerBase {

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'negate';
  }

  /**
   * {@inheritdoc}
   */
  public function doTransform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $source_field_name = $strategy['source'];
    $source_field_data = $source->get($source_field_name)->getValue();

    foreach ($source_field_data as $item) {
      $transformed = [];
      foreach ($strategy['properties'] as $target_prop => $source_prop) {
        if (array_key_exists($source_prop, $item)) {
          $transformed[$target_prop] = (string) (int) !$item[$source_prop];
        }
      }

      $output[] = $transformed;
    }

    return $output;
  }

}
