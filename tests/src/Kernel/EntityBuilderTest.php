<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\node\Entity\Node;

/**
 * Tests the entity builder service.
 *
 * @group dacem_sync
 */
class EntityBuilderTest extends EntityBuilderTestBase {

  /**
   * Tests the createTargetFromSource() method.
   */
  public function testCreateTargetFromSource(): void {
    // Create a duplicate to have an unmapped UUID.
    $source = $this->source->createDuplicate();

    $builder = $this->container->get('dacem_sync.entity_builder');

    $builder->createTargetFromSource('node', 'clone', $source, $this->map);

    $clones = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->loadByProperties([EntityManager::BASE_FIELD => $source->uuid()]);

    $this->assertCount(1, $clones);

    $target = reset($clones);

    $this->assertEquals($source->label(), $target->label());
  }

  /**
   * Tests the updateTargetFromSource() method.
   */
  public function testUpdateTargetFromSource(): void {
    $originalRevisionId = $this->target->getRevisionId();

    $builder = $this->container->get('dacem_sync.entity_builder');

    $builder->updateTargetFromSource($this->target, $this->source, $this->map);

    $target = Node::load($this->target->id());

    $this->assertNotNull($target);

    $this->assertEquals(
      'First of multiple',
      $target->get('field_multiple')->get(0)->getValue()['value']
    );

    $this->assertEquals(
      'Last of multiple',
      $target->get('field_multiple')->get(1)->getValue()['value']
    );

    $this->assertGreaterThan(
      $originalRevisionId,
      $target->getRevisionId()
    );
  }

  /**
   * Tests the buildFromSource() method.
   */
  public function testBuildFromSource(): void {
    $entity_builder = $this->container->get('dacem_sync.entity_builder');

    $built = $entity_builder->buildFromSource($this->source, $this->map);

    $expected = [
      'title' => [
        ['value' => 'Example'],
      ],
      'field_common' => [],
      'field_multiple' => [
        ['value' => 'First of multiple'],
        ['value' => 'Last of multiple'],
      ],
    ];

    ksort($built);
    ksort($expected);

    $this->assertEquals($expected, $built);
  }

  /**
   * Tests the extractFromTarget() method.
   */
  public function testExtractFromTarget(): void {
    $entity_builder = $this->container->get('dacem_sync.entity_builder');

    $extracted = $entity_builder->extractFromTarget($this->target, $this->map);

    $expected = [
      'title' => [
        ['value' => 'Clone'],
      ],
      'field_common' => [
        ['value' => 'Common value'],
      ],
      'field_multiple' => [
        ['value' => 'Last of multiple'],
        ['value' => 'First of multiple'],
      ],
    ];

    ksort($extracted);
    ksort($expected);

    $this->assertEquals($expected, $extracted);
  }

}
