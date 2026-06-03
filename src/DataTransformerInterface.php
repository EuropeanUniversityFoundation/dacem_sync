<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines an interface for data transformers.
 */
interface DataTransformerInterface {

  /**
   * Transforms the data.
   */
  public function transform(ContentEntityInterface $source, array $strategy): array;

}
