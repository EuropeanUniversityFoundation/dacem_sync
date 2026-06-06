<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformer\GroupInstitution;
use Drupal\dacem_sync\EntityManager;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the GroupInstitution data transformer.
 *
 * @group dacem_sync
 */
class GroupInstitutionTest extends UnitTestCase {

  /**
   * Tests that the data transformer can assign field values.
   */
  public function testTransformerAssignsFieldValues(): void {

    $entity = $this->createMock(ContentEntityInterface::class);

    $strategy = [];

    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->method('getGroupHeiId')->with($entity)->willReturn(1);

    $transformer = new GroupInstitution($entity_manager);

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'target_id' => 1,
        ],
      ],
      $result
    );
  }

}
