<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\node\Entity\Node;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests CourseSyncHandler.
 *
 * @group dacem_sync
 */
class CourseSyncHandlerOnUpdateTest extends CourseSyncHandlerTestBase {

  /**
   * Tests the onUpdate workflow.
   */
  public function testOnUpdate(): void {
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

    $course = LearningOpportunitySpecification::load(2);
    $vid_on_insert = $course->getRevisionId();

    $node = Node::load(2);
    $node->set('field_credits', ['value' => 12]);
    $node->save();

    // Fifth item: course update.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $node = Node::load(2);
    $course = LearningOpportunitySpecification::load(2);
    $vid_on_update = $course->getRevisionId();

    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $values = $course->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $course->label(),
    );

    // 'field_credits' to 'course__ects'.
    $expected = [['value' => 12]];
    $this->assertEquals($expected, $values['course__ects']);
  }

  /**
   * Tests the onUpdate workflow without a mapped field.
   */
  public function testOnUpdateWithoutMappedField(): void {
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

    $course = LearningOpportunitySpecification::load(2);
    $vid_on_insert = $course->getRevisionId();
    $values_on_insert = $course->toArray();

    $node = Node::load(2);
    $node->set('field_iec_coordinator', ['value' => 'Unknown']);
    $node->save();

    // Fifth item: course update.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $course = LearningOpportunitySpecification::load(2);
    $vid_on_update = $course->getRevisionId();
    $values_on_update = $course->toArray();

    $this->assertEquals($vid_on_insert, $vid_on_update);
    $this->assertEquals($values_on_insert, $values_on_update);
  }

  /**
   * Tests the onUpdate workflow with a content translation.
   */
  public function testOnUpdateWithTranslation(): void {
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

    $course = LearningOpportunitySpecification::load(2);
    $vid_on_insert = $course->getRevisionId();

    $node = Node::load(2);
    $node->addTranslation('pt-pt', [
      'title' => 'Exemplo de Disciplina',
    ]);
    $node->save();

    // Fifth item: course update.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $node = Node::load(2);
    $course = LearningOpportunitySpecification::load(2);
    $vid_on_update = $course->getRevisionId();

    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $values = $course->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $course->label(),
    );

    // From 'title' to 'title', with translation.
    $expected = [
      [
        'string' => 'Example Course',
        'lang' => 'en',
      ],
      [
        'string' => 'Exemplo de Disciplina',
        'lang' => 'pt-PT',
      ],
    ];
    $this->assertEquals($expected, $values['title'], 'title');
  }

}
