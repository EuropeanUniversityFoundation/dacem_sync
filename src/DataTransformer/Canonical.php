<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a Canonical data transformer.
 */
class Canonical implements DataTransformerInterface {

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    /** @var  \Drupal\Core\Entity\ContentEntityInterface $source */
    $source_field_name = $strategy['source'];
    $source_field_data = $source->get($source_field_name)->getValue();

    foreach ($source_field_data as $item) {
      $transformed = [];
      foreach ($strategy['properties'] as $target_prop => $source_prop) {
        $transformed[$target_prop] = $item[$source_prop];
      }

      $output[] = $transformed;
    }

    return $output;
  }

}
