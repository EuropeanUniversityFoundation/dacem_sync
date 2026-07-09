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
class OunitSyncHandlerOnInsertTest extends OunitSyncHandlerTestBase {

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

}
