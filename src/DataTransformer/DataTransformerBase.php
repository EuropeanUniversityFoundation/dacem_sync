<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;
use Drupal\dacem_sync\Exception\MissingRequiredFieldException;

/**
 * Defines an interface for data transformers.
 */
abstract class DataTransformerBase implements DataTransformerInterface {

  public const GLUE = '.';
  public const SEPARATOR = '/';

  /**
   * Returns the ID of the transformer.
   */
  abstract public function id(): string;

  /**
   * Transforms the data.
   */
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $this->validateRequiredFields($source, $strategy);

    return $this->doTransform($source, $strategy);
  }

  /**
   * Transforms the data.
   */
  abstract protected function doTransform(ContentEntityInterface $source, array $strategy): array;

  /**
   * Validates required fields.
   */
  protected function validateRequiredFields(ContentEntityInterface $source, array $strategy): void {
    if (array_key_exists('required', $strategy) && $strategy['required']) {
      $source_fields = is_array($strategy['source'])
        ? $strategy['source']
        : [$strategy['source']];

      foreach ($source_fields as $source_field) {
        $parts = explode(self::GLUE, $source_field);

        if (count($parts) === 1) {
          $field_name = $parts[0];
          $this->validateRequiredField($source, $field_name);
        }
        elseif (count($parts) === 3) {
          $reference = $parts[0];
          // $entity_type_id = $parts[1];
          $field_name = $parts[2];

          $this->validateRequiredField($source, $reference);

          $reference_field = $source->get($reference);
          /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $reference_field */
          $referenced_entities = $reference_field->referencedEntities();

          foreach ($referenced_entities as $entity) {
            /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
            $this->validateRequiredField($entity, $field_name);
          }
        }
        else {
          throw new \RuntimeException();
        }
      }
    }
  }

  /**
   * Validates an individual required field.
   */
  protected function validateRequiredField(ContentEntityInterface $entity, string $field): void {
    if (!$entity->hasField($field) || $entity->get($field)->isEmpty()) {
      throw new MissingRequiredFieldException(
        sprintf(
          'Missing required field "%s" in %s.',
          $field,
          $entity->toUrl()->setAbsolute()->toString()
        ),
        [
          'missing' => [$field],
          'entity_type_id' => $entity->getEntityTypeId(),
          'uuid' => $entity->uuid(),
        ]
      );
    }
  }

}
