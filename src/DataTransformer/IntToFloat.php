<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines an IntToFloat data transformer.
 */
class IntToFloat implements DataTransformerInterface {

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'int_to_float';
  }

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $source_field_name = $strategy['source'];
    $source_field_data = $source->get($source_field_name)->getValue();

    foreach ($source_field_data as $item) {
      $transformed = [];
      foreach ($strategy['properties'] as $target_prop => $source_prop) {
        if (array_key_exists($source_prop, $item)) {
          $int_value = (int) $item[$source_prop];
          $transformed[$target_prop] = (float) $int_value;
        }
      }

      $output[] = $transformed;
    }

    return $output;
  }

}
