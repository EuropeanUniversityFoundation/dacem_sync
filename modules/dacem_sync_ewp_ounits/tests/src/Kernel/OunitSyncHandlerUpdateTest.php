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
class OunitSyncHandlerUpdateTest extends OunitSyncHandlerTestBase {

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

    $ounit = Ounit::load(1);
    $vid_on_insert = $ounit->getRevisionId();

    $node = Node::load(1);
    $node->set('field_ou_abbreviation', ['value' => 'Example Unit']);

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

    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $values = $ounit->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $ounit->label(),
    );

    // From 'field_ou_abbreviation' to 'abbreviation'.
    $expected = [['value' => 'Example Unit']];
    $this->assertEquals($expected, $values['abbreviation'], 'abbreviation');
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

    $ounit = Ounit::load(1);
    $vid_on_insert = $ounit->getRevisionId();
    $values_on_insert = $ounit->toArray();

    $node = Node::load(1);
    $node->set('field_ou_description', ['value' => 'Lorem ipsum etc.']);

    $item = $queue->claimItem();

    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $ounit = Ounit::load(1);
    $vid_on_update = $ounit->getRevisionId();
    $values_on_update = $ounit->toArray();

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

    $ounit = Ounit::load(1);
    $vid_on_insert = $ounit->getRevisionId();

    $node = Node::load(1);
    $node->addTranslation('pt-pt', [
      'title' => 'Exemplo de Unidade Orgânica',
    ]);

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

    $this->assertGreaterThan($vid_on_insert, $vid_on_update);

    $values = $ounit->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $ounit->label(),
    );

    // From 'title' to 'name', with translation.
    $expected = [
      [
        'string' => 'Example Organizational Unit',
        'lang' => 'en',
      ],
      [
        'string' => 'Exemplo de Unidade Orgânica',
        'lang' => 'pt-PT',
      ],
    ];
    $this->assertEquals($expected, $values['name'], 'name');
  }

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
