<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for data transformers.
 */
interface DataTransformerInterface {

  /**
   * Returns the ID of the transformer.
   */
  public function id(): string;

  /**
   * Transforms the data.
   */
  public function transform(ContentEntityInterface $source, array $strategy): array;

}
