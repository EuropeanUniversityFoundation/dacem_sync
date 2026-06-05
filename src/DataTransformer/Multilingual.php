<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Defines a Multilingual data transformer.
 */
class Multilingual implements DataTransformerInterface {

  /**
   * {@inheritdoc}
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $output = [];

    $source_field_name = $strategy['source'];

    $translations = $source->getTranslationLanguages();

    foreach ($translations as $langcode => $language) {
      $translation_field_data = $source->getTranslation($langcode)
        ->get($source_field_name)
        ->getValue();

      foreach ($translation_field_data as $item) {
        $transformed = [];

        $item['langcode'] = $langcode;
        foreach ($strategy['properties'] as $target_prop => $source_prop) {
          $transformed[$target_prop] = $item[$source_prop];
        }

        $output[] = $transformed;
      }
    }

    return $output;
  }

}
