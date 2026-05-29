<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

/**
 * Defines an interface for field mappings.
 */
interface FieldMappingInterface {

  /**
   * Returns the field mapping for a source-target pair.
   */
  public function mapping(): array;

}
