<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel\DataTransformer;

use Drupal\KernelTests\KernelTestBase;
use Drupal\dacem_sync\DataTransformerInterface;

/**
 * Tests the data transformer resolver service.
 *
 * @group dacem_sync
 */
class DataTransformerResolverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'dacem_sync',
    'dacem_sync_occ_entities',
  ];

  /**
   * Tests that all provided data transformers are collected by the resolver.
   */
  public function testDataResolversAreCollected(): void {
    $collector = $this->container->get('dacem_sync.data_transformer_resolver');

    $academic_term = $collector->get('academic_term');
    $this->assertInstanceOf(DataTransformerInterface::class, $academic_term);

    $negate = $collector->get('negate');
    $this->assertInstanceOf(DataTransformerInterface::class, $negate);

    $related_programme = $collector->get('related_programme');
    $this->assertInstanceOf(DataTransformerInterface::class, $related_programme);
  }

}
