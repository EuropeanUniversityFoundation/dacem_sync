<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\Exception;

/**
 * Exception thrown when a required field is missing.
 */
class MissingRequiredFieldException extends \RuntimeException {

  public function __construct(
    string $message,
    protected array $missing = [],
    ?\Throwable $previous = NULL,
  ) {
    parent::__construct($message, 0, $previous);
  }

  /**
   * Returns items missing required fields.
   */
  public function getMissing(): array {
    return $this->missing;
  }

}
