<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync\DataTransformer\Canonical;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Canonical data transformer.
 */
class CanonicalTest extends UnitTestCase {

  /**
   * Tests that the data transformer can copy field values.
   */
  public function testTransformerCopiesFieldValues(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')->willReturn([
        [
          'value' => 'My value',
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

    $transformer = new Canonical();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => 'My value',
        ],
      ],
      $result
    );
  }

  /**
   * Tests that the data transformer can handle multiple field values.
   */
  public function testTransformerHandlesMultipleFieldValues(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')->willReturn([
        [
          'value' => 'My value',
        ],
        [
          'value' => 'Another value',
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

    $transformer = new Canonical();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => 'My value',
        ],
        [
          'value' => 'Another value',
        ],
      ],
      $result
    );
  }

  /**
   * Tests that the data transformer can rename properties.
   */
  public function testTransformerRenamesProperties(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')->willReturn([
        [
          'value' => 'My value',
        ],
      ]);

    $entity = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->with('field_my_value')->willReturn($field);

    $strategy = [
      'properties' => [
        'prop' => 'value',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Canonical();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'prop' => 'My value',
        ],
      ],
      $result
    );
  }

  /**
   * Tests that the data transformer can handle multiple properties.
   */
  public function testTransformerHandlesMultipleProperties(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')  ->willReturn([
        [
          'uri' => 'https://example.com',
          'title' => 'Example',
          'options' => [
            'key' => 'value',
          ],
        ],
      ]);

    $entity = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->with('field_my_value')->willReturn($field);

    $strategy = [
      'properties' => [
        'uri' => 'uri',
        'title' => 'title',
        'options' => 'options',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Canonical();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'uri' => 'https://example.com',
          'title' => 'Example',
          'options' => [
            'key' => 'value',
          ],
        ],
      ],
      $result
    );
  }

  /**
   * Tests that the data transformer can handle missing properties.
   */
  public function testTransformerHandlesMissingProperties(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')->willReturn([
        [
          'value' => 'My value',
        ],
      ]);

    $entity = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->with('field_my_value')->willReturn($field);

    $strategy = [
      'properties' => [
        'value' => 'value',
        'options' => 'options',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Canonical();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'value' => 'My value',
        ],
      ],
      $result
    );
  }

}
