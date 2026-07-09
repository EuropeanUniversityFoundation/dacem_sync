<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\node\Entity\Node;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests ProgrammeSyncHandler.
 *
 * @group dacem_sync
 */
class ProgrammeSyncHandlerOnUpdateTest extends ProgrammeSyncHandlerTestBase {

  /**
   * Tests the onUpdate workflow.
   */
  public function testOnUpdate(): void {
    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $programme = LearningOpportunitySpecification::load(1);
    $vid_on_insert = $programme->getRevisionId();

    $node = Node::load(1);
    $node->set('field_programme_abbreviation', ['value' => 'E. Programme']);

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

    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $values = $programme->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $programme->label(),
    );

    // 'field_programme_abbreviation' to 'programme__abbreviation'.
    $expected = [
      [
        'string' => 'E. Programme',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['programme__abbreviation']);
  }

  /**
   * Tests the onUpdate workflow without a mapped field.
   */
  public function testOnUpdateWithoutMappedField(): void {
    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $programme = LearningOpportunitySpecification::load(1);
    $vid_on_insert = $programme->getRevisionId();
    $values_on_insert = $programme->toArray();

    $node = Node::load(1);
    $node->set('field_programme_qualification', ['value' => 'BSc.']);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $programme = LearningOpportunitySpecification::load(1);
    $vid_on_update = $programme->getRevisionId();
    $values_on_update = $programme->toArray();

    $this->assertEquals($vid_on_insert, $vid_on_update);
    $this->assertEquals($values_on_insert, $values_on_update);
  }

  /**
   * Tests the onUpdate workflow with a content translation.
   */
  public function testOnUpdateWithTranslation(): void {
    $queue = $this->container->get('queue')
      ->get(DacemSyncQueueWorker::QUEUE_NAME);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $programme = LearningOpportunitySpecification::load(1);
    $vid_on_insert = $programme->getRevisionId();

    $node = Node::load(1);
    $node->addTranslation('pt-pt', [
      'title' => 'Exemplo de Programa Curricular',
    ]);

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

    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $values = $programme->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $programme->label(),
    );

    // From 'title' to 'title', with translation.
    $expected = [
      [
        'string' => 'Example Degree Programme',
        'lang' => 'en',
      ],
      [
        'string' => 'Exemplo de Programa Curricular',
        'lang' => 'pt-PT',
      ],
    ];
    $this->assertEquals($expected, $values['title'], 'title');
  }

}
