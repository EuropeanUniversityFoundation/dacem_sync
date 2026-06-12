<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel\DataTransformer;

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
  ];

  /**
   * Tests that all provided data transformers are collected by the resolver.
   */
  public function testDataResolversAreCollected(): void {
    $collector = $this->container->get('dacem_sync.data_transformer_resolver');

    $canonical = $collector->get('canonical');
    $this->assertInstanceOf(DataTransformerInterface::class, $canonical);

    $fraction = $collector->get('fraction');
    $this->assertInstanceOf(DataTransformerInterface::class, $fraction);

    $group_hei = $collector->get('group_hei');
    $this->assertInstanceOf(DataTransformerInterface::class, $group_hei);

    $int_to_float = $collector->get('int_to_float');
    $this->assertInstanceOf(DataTransformerInterface::class, $int_to_float);

    $multilingual = $collector->get('multilingual');
    $this->assertInstanceOf(DataTransformerInterface::class, $multilingual);

    $referenced_entity_field = $collector->get('referenced_entity_field');
    $this->assertInstanceOf(DataTransformerInterface::class, $referenced_entity_field);
  }

}
