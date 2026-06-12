<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync_occ_entities\DataTransformer\AcademicTerm;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the AcademicTerm data transformer.
 *
 * @group dacem_sync
 */
class AcademicTermTest extends UnitTestCase {

  /**
   * Tests that the data transformer can concatenate field values.
   */
  public function testTransformerConcatenates(): void {

    $programme = $this->createMock(ContentEntityInterface::class);
    $denominator = $this->createMock(FieldItemListInterface::class);
    $denominator->method('getValue')->willReturn([
        [
          'value' => 4,
        ],
    ]);
    $programme->method('get')->with('field_total')->willReturn($denominator);

    $numerator = $this->createMock(FieldItemListInterface::class);
    $numerator->method('getValue')->willReturn([
        [
          'value' => 1,
        ],
        [
          'value' => 3,
        ],
    ]);

    $reference = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $reference->method('referencedEntities')->willReturn([$programme]);

    $entity = $this->createMock(ContentEntityInterface::class);
    $entity->method('get')->willReturnMap([
      ['field_items', $numerator],
      ['field_programme', $reference],
    ]);

    $strategy = [
      'properties' => [
        'value' => 'value',
      ],
      'source' => [
        'field_items',
        'field_programme.programme.field_total',
      ],
    ];

    $transformer = new AcademicTerm();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => '1/4',
        ],
        [
          'value' => '3/4',
        ],
      ],
      $result
    );
  }

}
