<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_occ_entities\SyncHandler\CourseSyncHandler;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests CourseSyncHandler.
 *
 * @group dacem_sync
 */
class CourseSyncHandlerOnInsertAfterDeleteTest extends CourseSyncHandlerTestBase {

  /**
   * Tests the onInsert workflow after a deletion.
   */
  public function testOnInsertAfterDelete(): void {
    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    // First item: programme insert.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    // Second item: programme update (group relationship).
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    // Third item: course insert.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    // Fourth item: course update (group relationship).
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $node = Node::load(2);
    $course = LearningOpportunitySpecification::load(2);
    $vid_on_insert = $course->getRevisionId();

    // From source UUID to 'source_uuid'.
    $source_uuid = $course->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);

    // Extract some critical data before deletion.
    $node_data = $this->cleanNodeData($node);
    $relationship_data = $this->cleanRelationshipData($node);

    $node->delete();

    // Fifth item: course delete.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $node = Node::load(2);
    $course = LearningOpportunitySpecification::load(2);
    $vid_on_update = $course->getRevisionId();

    $this->assertNull($node);
    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $status = $course->get(CourseSyncHandler::TARGET_OFF_SWITCH)->value;
    $this->assertEquals(CourseSyncHandler::TARGET_OFF_STATE, $status);

    // Recreate the node.
    $node = Node::create($node_data);
    $node->save();

    // Recreate the group relationship.
    $relationship_data['entity_id'] = $node->id();
    $relationship = \Drupal::entityTypeManager()
      ->getStorage('group_relationship')
      ->create($relationship_data);
    $relationship->save();

    // Sixth item: course insert.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    // Seventh item: course update (group relationship).
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $new_course = LearningOpportunitySpecification::load(3);
    $this->assertNull($new_course);

    $node = Node::load(3);
    $course = LearningOpportunitySpecification::load(2);
    $source_uuid = $course->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertNotNull($node);
    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);
  }

  /**
   * Helper method to obtain clean data to recreate a node.
   */
  private function cleanNodeData(NodeInterface $node): array {
    $data = $node->toArray();

    foreach ([
      'nid',
      'uuid',
      'vid',
      'revision_uid',
      'revision_log',
      'revision_timestamp',
      'changed',
      'created',
    ] as $field) {
      unset($data[$field]);
    }

    return $data;
  }

  /**
   * Helper method to obtain clean data to recreate a group relationship.
   */
  private function cleanRelationshipData(NodeInterface $node): array {
    $plugin_id = implode(':', [
      'group_node',
      CourseSyncHandler::SOURCE_BUNDLE,
    ]);

    $relationship_type = \Drupal::entityTypeManager()
      ->getStorage('group_relationship_type')
      ->loadByProperties([
        'group_type' => EntityManager::GROUP_TYPE_ID,
        'content_plugin' => $plugin_id,
      ]);

    $relationship_type = reset($relationship_type);

    $relationship = \Drupal::entityTypeManager()
      ->getStorage('group_relationship')
      ->loadByProperties([
        'type' => $relationship_type->id(),
        'entity_id' => $node->id(),
      ]);

    $relationship = reset($relationship);
    $relationship_data = $relationship->toArray();

    $data = [
      'type' => $relationship_data['type'],
      'gid' => $relationship_data['gid'],
    ];

    return $data;
  }

}
