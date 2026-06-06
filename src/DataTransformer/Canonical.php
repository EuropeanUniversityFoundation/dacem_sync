<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a Canonical data transformer.
 */
class Canonical implements DataTransformerInterface {

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'canonical';
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
          $transformed[$target_prop] = $item[$source_prop];
        }
      }

      $output[] = $transformed;
    }

    return $output;
  }

}
