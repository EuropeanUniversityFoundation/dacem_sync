<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_ewp_ounits\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_ewp_ounits\SyncHandler\OunitSyncHandler;
use Drupal\ewp_ounits\Entity\Ounit;
use Drupal\node\Entity\Node;

/**
 * Tests OunitSyncHandler.
 *
 * @group dacem_sync
 */
class OunitSyncHandlerTest extends OunitSyncHandlerTestBase {

  /**
   * The sync handler.
   *
   * @var \Drupal\dacem_sync_ewp_ounits\SyncHandler\OunitSyncHandler
   */
  protected $syncHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create an Institution.
    $entity_type_manager = $this->container->get('entity_type.manager');

    $hei = $entity_type_manager->getStorage('hei')->create([
      'label' => 'Example Institution',
      'hei_id' => 'example.com',
      'name' => [
        [
          'string' => 'Example Institution',
          'lang' => 'en',
        ],
      ],
    ]);
    $hei->save();

    // Create a Group referencing the Institution.
    $group = $entity_type_manager->getStorage('group')->create([
      'type' => EntityManager::GROUP_TYPE_ID,
      'label' => 'Example Group',
      EntityManager::GROUP_HEI_REF => $hei->id(),
    ]);
    $group->save();

    // Create a Node.
    $node = Node::create([
      'type' => OunitSyncHandler::SOURCE_BUNDLE,
      'title' => 'Example Organizational Unit',
      'field_ou_abbreviation' => 'Example',
      'field_ou_code' => 'OUX-1',
      'field_ou_description' => 'Description of this Organizational Unit.',
      'field_ou_web' => [
        'uri' => 'https://example.com',
        'title' => 'example.com',
      ],
      'status' => 1,
    ]);
    $node->save();

    // Add a Relationship between the Group and the Node.
    $plugin_id = implode(':', ['group_node', OunitSyncHandler::SOURCE_BUNDLE]);

    $relationship_type = \Drupal::entityTypeManager()
      ->getStorage('group_relationship_type')
      ->loadByProperties([
        'group_type' => EntityManager::GROUP_TYPE_ID,
        'content_plugin' => $plugin_id,
      ]);

    $relationship_type = reset($relationship_type);

    $relationship = \Drupal::entityTypeManager()
      ->getStorage('group_relationship')
      ->create([
        'type' => $relationship_type->id(),
        'gid' => $group->id(),
        'entity_id' => $node->id(),
      ]);

    $relationship->save();
  }

  /**
   * Tests the onInsert workflow.
   */
  public function testOnInsert(): void {
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
    $values = $ounit->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $ounit->label(),
    );

    // From 'title' to 'name'.
    $expected = [
      [
        'string' => 'Example Organizational Unit',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['name'], 'name');

    // From 'field_ou_abbreviation' to 'abbreviation'.
    $expected = [['value' => 'Example']];
    $this->assertEquals($expected, $values['abbreviation'], 'abbreviation');

    // From 'uuid' to 'ounit_id'.
    $ounit_id = $values['ounit_id'][0]['value'];
    $this->assertEquals($node->uuid(), $ounit_id, 'ounit_id');

    // From 'field_ou_code' to 'ounit_code'.
    $expected = [['value' => 'OUX-1']];
    $this->assertEquals($expected, $values['ounit_code'], 'ounit_code');

    // From 'field_ou_web' to 'website_url'.
    $expected = [
      [
        'uri' => 'https://example.com',
        'title' => 'example.com',
        'options' => [],
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['website_url'], 'website_url');

    // From Group reference to 'parent_hei'.
    $expected = [['target_id' => '1']];
    $this->assertEquals($expected, $values['parent_hei'], 'parent_hei');

    // From source UUID to 'source_uuid'.
    $source_uuid = $ounit->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);
  }

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
   * Tests the onIDelete workflow.
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
