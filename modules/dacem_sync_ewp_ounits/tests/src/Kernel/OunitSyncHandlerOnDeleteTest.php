<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_ewp_ounits\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\ewp_ounits\Entity\Ounit;
use Drupal\node\Entity\Node;

/**
 * Tests OunitSyncHandler.
 *
 * @group dacem_sync
 */
class OunitSyncHandlerOnDeleteTest extends OunitSyncHandlerTestBase {

  /**
   * Tests the onDelete workflow.
   */
  public function testOnDelete(): void {
    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $node = Node::load(1);
    $ounit = Ounit::load(1);
    $vid_on_insert = $ounit->getRevisionId();

    // From source UUID to 'source_uuid'.
    $source_uuid = $ounit->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);

    $node->delete();

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $node = Node::load(1);
    $ounit = Ounit::load(1);
    $vid_on_update = $ounit->getRevisionId();

    $this->assertNull($node);
    $this->assertGreaterThan($vid_on_insert, $vid_on_update);
    $this->assertFalse($ounit->isPublished());
  }

}
