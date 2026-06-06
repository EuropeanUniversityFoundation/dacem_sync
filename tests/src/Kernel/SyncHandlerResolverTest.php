<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel\SyncHandler;

use Drupal\KernelTests\KernelTestBase;
use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Tests the sync handler resolver service.
 *
 * @group dacem_sync
 */
class SyncHandlerResolverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'dacem_sync',
    'dacem_sync_sync_handler_test',
  ];

  /**
   * Tests that all provided sync handlers are collected by the resolver.
   */
  public function testSyncHandlersAreCollected(): void {
    $collector = $this->container->get('dacem_sync.sync_handler_resolver');

    $neutral = $collector->get('neutral');

    $this->assertInstanceOf(SyncHandlerInterface::class, $neutral);
  }

}
