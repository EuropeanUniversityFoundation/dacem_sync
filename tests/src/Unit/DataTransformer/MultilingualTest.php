<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dacem_sync\DataTransformer\Multilingual;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Multilingual data transformer.
 *
 * @group dacem_sync
 */
class MultilingualTest extends UnitTestCase {

  /**
   * Tests that the data transformer attaches langcode.
   */
  public function testTransformerAttachesLangcode(): void {

    $field = $this->createMock(FieldItemListInterface::class);

    $field->method('getValue')->willReturn([
        [
          'value' => 'My value',
        ],
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);

    $en_lang = $this->createMock(LanguageInterface::class);

    $entity->method('getTranslationLanguages')->willReturn([
      'en' => $en_lang,
    ]);

    $translation = $this->createMock(ContentEntityInterface::class);

    $entity->method('getTranslation')->with('en')->willReturn($translation);

    $translation->method('get')->with('field_my_value')->willReturn($field);

    $strategy = [
      'properties' => [
        'string' => 'value',
        'lang' => 'langcode',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Multilingual();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'string' => 'My value',
          'lang' => 'en',
        ],
      ],
      $result
    );
  }

  /**
   * Tests that the data transformer handles translations.
   */
  public function testTransformerHandlesTranslations(): void {

    $en_field = $this->createMock(FieldItemListInterface::class);
    $en_field->method('getValue')->willReturn([
        [
          'value' => 'My value',
        ],
    ]);

    $fr_field = $this->createMock(FieldItemListInterface::class);
    $fr_field->method('getValue')->willReturn([
      [
        'value' => 'Ma valeur',
      ],
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);
    $translation = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->with('field_my_value')->willReturn($en_field);
    $translation->method('get')->with('field_my_value')->willReturn($fr_field);

    $en_lang = $this->createMock(LanguageInterface::class);
    $fr_lang = $this->createMock(LanguageInterface::class);

    $entity->method('getTranslationLanguages')->willReturn([
      'en' => $en_lang,
      'fr' => $fr_lang,
    ]);

    $entity->method('getTranslation')->willReturnMap([
      ['en', $entity],
      ['fr', $translation],
    ]);

    $strategy = [
      'properties' => [
        'string' => 'value',
        'lang' => 'langcode',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Multilingual();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'string' => 'My value',
          'lang' => 'en',
        ],
        [
          'string' => 'Ma valeur',
          'lang' => 'fr',
        ],
      ],
      $result
    );
  }

  /**
   * Tests that the data transformer handles multiple values.
   */
  public function testTransformerHandlesMultipleValues(): void {

    $en_field = $this->createMock(FieldItemListInterface::class);
    $en_field->method('getValue')->willReturn([
      [
        'value' => 'My value',
      ],
      [
        'value' => 'Another value',
      ],
    ]);

    $fr_field = $this->createMock(FieldItemListInterface::class);
    $fr_field->method('getValue')->willReturn([
      [
        'value' => 'Ma valeur',
      ],
      [
        'value' => 'Une autre valeur',
      ],
    ]);

    $entity = $this->createMock(ContentEntityInterface::class);
    $translation = $this->createMock(ContentEntityInterface::class);

    $entity->method('get')->with('field_my_value')->willReturn($en_field);
    $translation->method('get')->with('field_my_value')->willReturn($fr_field);

    $en_lang = $this->createMock(LanguageInterface::class);
    $fr_lang = $this->createMock(LanguageInterface::class);

    $entity->method('getTranslationLanguages')->willReturn([
      'en' => $en_lang,
      'fr' => $fr_lang,
    ]);

    $entity->method('getTranslation')->willReturnMap([
      ['en', $entity],
      ['fr', $translation],
    ]);

    $strategy = [
      'properties' => [
        'string' => 'value',
        'lang' => 'langcode',
      ],
      'source' => 'field_my_value',
    ];

    $transformer = new Multilingual();

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'string' => 'My value',
          'lang' => 'en',
        ],
        [
          'string' => 'Another value',
          'lang' => 'en',
        ],
        [
          'string' => 'Ma valeur',
          'lang' => 'fr',
        ],
        [
          'string' => 'Une autre valeur',
          'lang' => 'fr',
        ],
      ],
      $result
    );
  }

}
