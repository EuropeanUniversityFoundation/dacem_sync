<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel;

use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_sync_handler_test\SyncHandler\NeutralSyncHandler;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the workflow to queue items for processing.
 *
 * @group dacem_sync
 */
class QueueWorkflowTest extends KernelTestBase {

  /**
   * The sync handler.
   *
   * @var \Drupal\dacem_sync_sync_handler_test\SyncHandler\NeutralSyncHandler
   */
  protected $syncHandler;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'text',
    'action',
    'user',
    'field',
    'node',
    'dacem_sync',
    'dacem_sync_sync_handler_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', [
      'node_access',
    ]);
    $this->installConfig(['node']);

    NodeType::create([
      'type' => 'example',
      'name' => 'Example',
    ])->save();

    $this->syncHandler = new NeutralSyncHandler();

    $this->container->set(
      'dacem_sync_sync_handler_test.neutral',
      $this->syncHandler
    );
  }

  /**
   * Tests the creation of a node queues an item for 'insert' handling.
   */
  public function testExampleCreatedQueuesItemForInsertHandling(): void {
    $node = Node::create([
      'type' => 'example',
      'title' => 'New example',
    ]);

    $node->save();

    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    /** @var object $item */

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $this->assertCount(1, $this->syncHandler->inserted);
    $this->assertEquals($node->uuid(), $this->syncHandler->inserted[0]['uuid']);
  }

  /**
   * Tests a change of a node queues an item for 'update' handling.
   */
  public function testExampleChangedQueuesItemForUpdateHandling(): void {
    $node = Node::create([
      'type' => 'example',
      'title' => 'Another example',
    ]);

    $uuid = $node->uuid();

    $node->save();

    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    /** @var object $item */

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $this->assertNotEmpty($this->syncHandler->inserted);
    $this->assertEmpty($this->syncHandler->updated);

    $node->set('title', 'Updated example');
    $node->save();

    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    /** @var object $item */

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $this->assertCount(1, $this->syncHandler->updated);
    $this->assertEquals($uuid, $this->syncHandler->updated[0]['uuid']);
  }

  /**
   * Tests the deletion of a node queues an item for 'delete' handling.
   */
  public function testExampleDeletedQueuesItemForDeleteHandling(): void {
    $node = Node::create([
      'type' => 'example',
      'title' => 'Throwaway example',
    ]);

    $uuid = $node->uuid();

    $node->save();

    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    /** @var object $item */

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $this->assertNotEmpty($this->syncHandler->inserted);
    $this->assertEmpty($this->syncHandler->deleted);

    $node->delete();

    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    /** @var object $item */

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $this->assertCount(1, $this->syncHandler->deleted);
    $this->assertEquals($uuid, $this->syncHandler->deleted[0]['uuid']);
  }

}
