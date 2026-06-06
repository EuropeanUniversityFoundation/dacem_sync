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

    $group_hei = $collector->get('group_hei');

    $this->assertInstanceOf(DataTransformerInterface::class, $group_hei);

    $multilingual = $collector->get('multilingual');

    $this->assertInstanceOf(DataTransformerInterface::class, $multilingual);
  }

}
