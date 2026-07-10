<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync_occ_entities\DataTransformer\RelatedProgramme;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the RelatedProgramme data transformer.
 *
 * @group dacem_sync
 */
class RelatedProgrammeTest extends UnitTestCase {

  /**
   * Tests that the data transformer can concatenate field values.
   */
  public function testTransformerConcatenates(): void {

    $programme = $this->createMock(ContentEntityInterface::class);

    $term_count = $this->createMock(FieldItemListInterface::class);
    $term_count->method('getValue')->willReturn([
        [
          'value' => 6,
        ],
    ]);

    $terms_per_year = $this->createMock(FieldItemListInterface::class);
    $terms_per_year->method('getValue')->willReturn([
        [
          'value' => 2,
        ],
    ]);

    $programme->method('id')->willReturn(1);
    $programme->method('get')->willReturnMap([
      ['field_length_of_programme', $term_count],
      ['field_number_of_terms', $terms_per_year],
    ]);

    $reference = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $reference->method('referencedEntities')->willReturn([$programme]);

    $type = $this->createMock(FieldItemListInterface::class);
    $type->method('getValue')->willReturn([
        [
          'value' => 'mandatory',
        ],
    ]);

    $year = $this->createMock(FieldItemListInterface::class);
    $year->method('getValue')->willReturn([
        [
          'value' => 2,
        ],
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);
    $entity->method('get')->willReturnMap([
      ['field_iec_programme', $reference],
      ['field_iec_type', $type],
      ['field_iec_year', $year],
    ]);

    $strategy = [
      'source' => [
        'field_iec_programme',
        'field_iec_type',
        'field_iec_year',
        'field_iec_programme.occ_los.field_length_of_programme',
        'field_iec_programme.occ_los.field_number_of_terms',
      ],
      'transformer' => 'related_programme',
    ];

    $transformer = new RelatedProgramme();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'target_id' => '1',
          'mandatory' => '1',
          'year' => '2/3',
        ],
      ],
      $result
    );
  }

}
