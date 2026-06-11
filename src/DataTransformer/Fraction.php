<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_sync_handler_test\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a Copy data transformer.
 */
class Copy implements DataTransformerInterface {

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'copy';
  }

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    return $source->get($strategy['source'])->getValue();
  }

}
