<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync\DataTransformer\ReferencedEntityField;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the ReferencedEntityField data transformer.
 *
 * @group dacem_sync
 */
class ReferencedEntityFieldTest extends UnitTestCase {

  /**
   * Tests that the data transformer can derefenence and extract field values.
   */
  public function testExtractsFieldValues(): void {
    $field_name = 'field_nested';

    $field_first = $this->createMock(FieldItemListInterface::class);
    $field_first->method('getValue')->willReturn([
        [
          'value' => 'My first value',
        ],
    ]);

    $term_first = $this->createMock(ContentEntityInterface::class);
    $term_first->method('get')->with($field_name)->willReturn($field_first);

    $field_last = $this->createMock(FieldItemListInterface::class);
    $field_last->method('getValue')->willReturn([
        [
          'value' => 'My last value',
        ],
    ]);

    $term_last = $this->createMock(ContentEntityInterface::class);
    $term_last->method('get')->with($field_name)->willReturn($field_last);

    $term_ref = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $term_ref->method('referencedEntities')->willReturn([
      $term_first,
      $term_last,
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);
    $entity->method('get')->with('field_term')->willReturn($term_ref);

    $strategy = [
      'properties' => [
        'value' => 'value',
      ],
      'source' => 'field_term.taxonomy_term.' . $field_name,
    ];

    $transformer = new ReferencedEntityField();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => 'My first value',
        ],
        [
          'value' => 'My last value',
        ],
      ],
      $result
    );
  }

}
