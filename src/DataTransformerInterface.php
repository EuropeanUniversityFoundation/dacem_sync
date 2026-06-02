<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

/**
 * Defines an interface for data transformers.
 */
interface DataTransformerInterface {

  /**
   * Transforms the data.
   */
  public function transform(mixed $source_data): mixed;

}
