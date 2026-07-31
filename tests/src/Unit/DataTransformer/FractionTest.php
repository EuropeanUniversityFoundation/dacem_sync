<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync\DataTransformer\Fraction;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Fraction data transformer.
 *
 * @group dacem_sync
 */
class FractionTest extends UnitTestCase {

  /**
   * Tests that the data transformer can concatenate field values.
   */
  public function testTransformerConcatenates(): void {

    $numerator = $this->createMock(FieldItemListInterface::class);

    $numerator->method('getValue')->willReturn([
        [
          'value' => 1,
        ],
    ]);

    $denominator = $this->createMock(FieldItemListInterface::class);

    $denominator->method('getValue')->willReturn([
        [
          'value' => 2,
        ],
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->willReturnMap([
      ['field_numerator', $numerator],
      ['field_denominator', $denominator],
    ]);

    $strategy = [
      'properties' => [
        'value' => 'result',
      ],
      'source' => [
        'field_numerator',
        'field_denominator',
      ],
    ];

    $transformer = new Fraction();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => '1/2',
        ],
      ],
      $result
    );
  }

}
