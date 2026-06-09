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

    $worker = $this->container->get('plugin.manager.queue_worker')
      ->createInstance(DacemSyncQueueWorker::PLUGIN_ID);

    $worker->processItem($item->data);

    $node = Node::load(1);
    $uuid = $node->uuid();
    $ounit = Ounit::load(1);
    $source_uuid = $ounit->get('source_uuid')->getValue()[0]['value'];
    $this->assertEquals($uuid, $source_uuid);

    $ounit_values = $ounit->toArray();
    // var_export($ounit_values);

    // From 'title' to 'label'.
    $this->assertEquals($node->label(), $ounit->label());

    // From 'title' to 'name'.
    $this->assertEquals([
      [
        'string' => 'Example Organizational Unit',
        'lang' => 'en',
      ],
    ], $ounit_values['name'], 'name');

    // From 'field_ou_abbreviation' to 'abbreviation'.
    $this->assertEquals([
      [
        'value' => 'Example',
      ],
    ], $ounit_values['abbreviation'], 'abbreviation');

    // From 'uuid' to 'ounit_id'.
    $this->assertEquals($uuid, $ounit_values['ounit_id'][0]['value'], 'ounit_id');

    // From 'field_ou_code' to 'ounit_code'.
    $this->assertEquals([
      [
        'value' => 'OUX-1',
      ],
    ], $ounit_values['ounit_code'], 'ounit_code');

    // From 'field_ou_web' to 'website_url'.
    $this->assertEquals([
      [
        'uri' => 'https://example.com',
        'title' => 'example.com',
        'options' => [],
        'lang' => 'en',
      ],
    ], $ounit_values['website_url'], 'website_url');

    // From Group reference to 'parent_hei'.
    $this->assertEquals([
      [
        'target_id' => '1',
      ],
    ], $ounit_values['parent_hei'], 'parent_hei');

  }

}
