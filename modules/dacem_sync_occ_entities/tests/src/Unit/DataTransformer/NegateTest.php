<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync_occ_entities\DataTransformer\Negate;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Negate data transformer.
 *
 * @group dacem_sync
 */
class NegateTest extends UnitTestCase {

  /**
   * Tests that the data transformer can copy field values.
   */
  public function testTransformerCopiesFieldValues(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')->willReturn([
        [
          'value' => TRUE,
        ],
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->with('field_my_value')->willReturn($field);

    $strategy = [
      'properties' => [
        'value' => 'value',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Negate();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => FALSE,
        ],
      ],
      $result
    );
  }

}
