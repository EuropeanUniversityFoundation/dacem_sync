<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a Fraction data transformer.
 */
class Fraction implements DataTransformerInterface {

  public const SEPARATOR = '/';

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'fraction';
  }

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $source_values = [];

    foreach ($strategy['source'] as $field_name) {
      $source_values[] = $source->get($field_name)->getValue()[0]['value'];
    }

    $numerator = (int) $source_values[0];
    $denominator = (int) $source_values[1];

    $result = implode(self::SEPARATOR, [$numerator, $denominator]);
    $target_prop = array_key_first($strategy['properties']);

    return [[$target_prop => $result]];
  }

}
