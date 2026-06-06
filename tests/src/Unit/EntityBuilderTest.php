<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dacem_sync\DataTransformerResolver;
use Drupal\dacem_sync\EntityBuilder;
use Drupal\dacem_sync\EntityManager;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the entity builder basic methods.
 *
 * @group dacem_sync
 */
class EntityBuilderTest extends UnitTestCase {

  /**
   * The entity builder.
   */
  protected EntityBuilder $entityBuilder;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $resolver = $this->createStub(DataTransformerResolver::class);
    $manager = $this->createStub(EntityManager::class);
    $logger = $this->createStub(LoggerChannelFactoryInterface::class);

    $this->entityBuilder = new EntityBuilder(
        $resolver,
        $manager,
        $logger,
    );
  }

  /**
   * Tests the diff() method.
   */
  public function testDiff(): void {
    $source_data = [
      'title' => [
        ['value' => 'Title'],
      ],
      'field_common' => [
        ['value' => 'Field in common'],
      ],
      'field_multiple' => [
        ['value' => 'First multiple value'],
        ['value' => 'Last multiple value'],
      ],
    ];

    $target_data = [
      'title' => [
        ['value' => 'Title'],
      ],
      'field_common' => [
        ['value' => 'Field in common'],
      ],
      'field_multiple' => [
        ['value' => 'Last multiple value'],
        ['value' => 'First multiple value'],
      ],
      'field_target_only' => [
        ['value' => 'Only exists in target'],
      ],
    ];

    $expected = [
      'field_multiple' => [
        ['value' => 'First multiple value'],
        ['value' => 'Last multiple value'],
      ],
      'field_target_only' => [],
    ];

    $diff = $this->entityBuilder->diff($source_data, $target_data);

    $this->assertEquals($expected, $diff);
  }

}
