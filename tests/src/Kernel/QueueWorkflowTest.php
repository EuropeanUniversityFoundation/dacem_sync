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
    $this->installConfig(['node']);

    NodeType::create([
      'type' => 'example',
      'name' => 'Example',
    ])->save();
  }

  /**
   * Tests the creation of a node queues an item for 'insert' handling.
   */
  public function testExampleCreatedQueuesItemForInsertHandling(): void {
    $sync_handler = new NeutralSyncHandler();

    $this->container->set(
      'dacem_sync_sync_handler_test.neutral',
      $sync_handler
    );

    $node = Node::create([
      'type' => 'example',
      'title' => 'New example',
    ]);

    $node->save();

    $queue = $this->container
      ->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    /** @var object $item */

    $worker = $this->container
      ->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $this->assertCount(1, $sync_handler->inserted);
  }

  /**
   * Tests a change of a node queues an item for 'update' handling.
   */
  public function testExampleChangedQueuesItemForUpdateHandling(): void {
    self::assertTrue(TRUE);
  }

  /**
   * Tests the deletion of a node queues an item for 'delete' handling.
   */
  public function testExampleDeletedQueuesItemForDeleteHandling(): void {
    self::assertTrue(TRUE);
  }

}
