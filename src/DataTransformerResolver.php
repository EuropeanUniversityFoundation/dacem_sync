<?php

declare(strict_types=1);

namespace Drupal\dacem_sync;

/**
 * Resolves data transformers based on tagged service ID.
 */
class DataTransformerResolver {

  /**
   * Data transformers discovered via service tags.
   *
   * @var array
   */
  protected $dataTransformers;

  /**
   * The constructor.
   *
   * @param DataTransformerInterface $data_transformer
   *   The data transformer.
   */
  public function addTransformer(DataTransformerInterface $data_transformer) {
    $this->dataTransformers[$data_transformer->id()] = $data_transformer;
  }

  /**
   * Returns a data transformer based on its ID.
   *
   * @param string $id
   *   The ID of the data transformer.
   *
   * @return \Drupal\dacem_sync\DataTransformerInterface
   *   The data transformer matching the provided ID.
   */
  public function get(string $id): DataTransformerInterface {
    if (!isset($this->dataTransformers[$id])) {
      throw new \InvalidArgumentException();
    }

    return $this->dataTransformers[$id];
  }

}
