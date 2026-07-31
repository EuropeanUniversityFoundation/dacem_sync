<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_sync_handler_test\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformer\DataTransformerBase;

/**
 * Defines a Copy data transformer.
 */
class Copy extends DataTransformerBase {

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'copy';
  }

  /**
   * {@inheritdoc}
   */
  public function doTransform(ContentEntityInterface $source, array $strategy): array {
    return $source->get($strategy['source'])->getValue();
  }

}
