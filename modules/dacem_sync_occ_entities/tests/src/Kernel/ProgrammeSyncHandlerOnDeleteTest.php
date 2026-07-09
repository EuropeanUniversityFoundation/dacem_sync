<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\node\Entity\Node;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests ProgrammeSyncHandler.
 *
 * @group dacem_sync
 */
class ProgrammeSyncHandlerOnDeleteTest extends ProgrammeSyncHandlerTestBase {

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
    $programme = LearningOpportunitySpecification::load(1);
    $vid_on_insert = $programme->getRevisionId();

    // From source UUID to 'source_uuid'.
    $source_uuid = $programme->get(EntityManager::BASE_FIELD)
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
    $programme = LearningOpportunitySpecification::load(1);
    $vid_on_update = $programme->getRevisionId();

    $this->assertNull($node);
    $this->assertGreaterThan($vid_on_insert, $vid_on_update);
    $this->assertEquals(0, $programme->get('status')->value);
  }

}
