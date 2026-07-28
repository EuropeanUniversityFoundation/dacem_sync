<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_ewp_ounits\SyncHandler\OunitSyncHandler;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\group\Entity\GroupRelationshipType;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests ProgrammeSyncHandler.
 *
 * @group dacem_sync
 */
class ProgrammeSyncHandlerOnInsertOunitSyncTest extends ProgrammeSyncHandlerTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'action',
    'user',
    'node',
    'field',
    'filter',
    'datetime',
    'link',
    'text',
    'options',
    'language',
    'locale',
    'content_translation',
    'entity',
    'flexible_permissions',
    'group',
    'gnode',
    'ewp_core',
    'ewp_flexible_address',
    'ewp_phone_number',
    'ewp_contact',
    'ewp_institutions',
    'entity_reference_validators',
    'ewp_ounits',
    'erasmus_subject_area_code',
    'isced_field',
    'elm_vocabulary_field',
    'occ_entities',
    'dacem_sync',
    'dacem_sync_occ_entities',
    'taxonomy',
    'views',
    // Additional submodule included only for this test.
    'dacem_sync_ewp_ounits',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installConfig([
      'ewp_ounits',
    ]);

    // Define an Organizational unit content type.
    $node_type = NodeType::create([
      'type' => OunitSyncHandler::SOURCE_BUNDLE,
      'name' => 'Organizational Unit',
    ]);
    $node_type->save();

    $plugin_id = implode(':', ['group_node', OunitSyncHandler::SOURCE_BUNDLE]);
    $sanitized = str_replace(':', '-', $plugin_id);
    $preferred_id = implode('-', [EntityManager::GROUP_TYPE_ID, $sanitized]);

    if (strlen($preferred_id) > 32) {
      $start = substr(EntityManager::GROUP_TYPE_ID, 0, 19);
      $end = substr(md5($preferred_id), 0, 12);
      $safe_id = implode('-', [$start, $end]);
    }
    else {
      $safe_id = $preferred_id;
    }

    // Define a Group Relationship type.
    $relationship_type = GroupRelationshipType::create([
      'id' => $safe_id,
      'group_type' => EntityManager::GROUP_TYPE_ID,
      'label' => 'Organizational Unit Content Relationship',
      'content_plugin' => $plugin_id,
    ]);
    $relationship_type->save();

    // Define the content type fields.
    FieldStorageConfig::create([
      'field_name' => 'field_ou_abbreviation',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_ou_abbreviation',
      'entity_type' => 'node',
      'bundle' => OunitSyncHandler::SOURCE_BUNDLE,
      'cardinality' => 1,
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_ou_code',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_ou_code',
      'entity_type' => 'node',
      'bundle' => OunitSyncHandler::SOURCE_BUNDLE,
      'cardinality' => 1,
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_ou_web',
      'entity_type' => 'node',
      'type' => 'link',
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_ou_web',
      'entity_type' => 'node',
      'bundle' => OunitSyncHandler::SOURCE_BUNDLE,
      'cardinality' => 1,
    ])->save();

    // Create an Organizational Unit Node.
    $node = Node::create([
      'type' => OunitSyncHandler::SOURCE_BUNDLE,
      'title' => 'Example Organizational Unit',
      'field_ou_code' => 'OU-01',
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
        'gid' => '1',
        'entity_id' => $node->id(),
      ]);

    $relationship->save();

    // Add the Organizational Unit reference to the Programme node.
    $programme = Node::load(1);
    $programme->set('field_programme_ou', $node->id());
    $programme->save();
  }

  /**
   * Tests the onInsert workflow.
   */
  public function testOnInsert(): void {
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

    // Third item: ounit insert.
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    // Fourth item: ounit update (group relationship).
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    // Fifth item: programme update (ounit).
    $item = $queue->claimItem();
    $this->assertNotNull($item);
    $this->assertNotFalse($item);
    $this->assertIsObject($item);
    $worker->processItem($item->data);

    $node = Node::load(1);
    $programme = LearningOpportunitySpecification::load(1);
    $values = $programme->toArray();

    // From Group reference to 'hei'.
    $expected = [['target_id' => '1']];
    $this->assertEquals($expected, $values['hei'], 'hei');

    // From source UUID to 'source_uuid'.
    $source_uuid = $programme->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);

    // With 'dacem_sync_ewp_ounits' enabled, 'ounit' will be synced.
    $expected = [['target_id' => '1']];
    $this->assertEquals($expected, $values['ounit'], 'ounit');
  }

}
