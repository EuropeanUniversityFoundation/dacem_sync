<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler;
use Drupal\node\Entity\Node;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests ProgrammeSyncHandler.
 *
 * @group dacem_sync
 */
class ProgrammeSyncHandlerTest extends ProgrammeSyncHandlerTestBase {

  /**
   * The sync handler.
   *
   * @var \Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler
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
      'type' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'title' => 'Example Degree Programme',
      'field_programme_code' => 'PROG-1',
      'field_credits' => 180,
      'field_eqf_level' => 6,
      'field_programme_abbreviation' => 'Example Programme',
      'field_programme_description' => 'Description of this Programme.',
      'field_learning_opportunity_type' => '79343569f3', // Educational programme.
      'field_programme_mode_of_study' => '72a0ab92fa', // Full time,
      'field_programme_mode_of_learning' => [
        '9191af2ed9', // Presential.
        '920fbb3cbe', // Online.
      ],
      'field_isced_f' => '0011', // Basic programmes and qualifications.
      'field_programme_language_of_inst' => [
        1, // English.
        2, // Portuguese (Portugal).
      ],
      'field_programme_learn_outcomes' => 'Learning outcomes of this Programme.',
      'field_length_of_programme' => 6,
      'field_number_of_terms' => 2,
      'field_programme_web' => [
        'uri' => 'https://example.com/programme/1',
        'title' => 'example.com/programme/1',
      ],
      'field_programme_start_date' => '2020-01-01',
      'field_programme_end_date' => '2030-12-31',
      'status' => 1,
    ]);
    $node->save();

    // Add a Relationship between the Group and the Node.
    $plugin_id = implode(':', [
      'group_node',
      ProgrammeSyncHandler::SOURCE_BUNDLE,
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
    $programme = LearningOpportunitySpecification::load(1);
    $values = $programme->toArray();

    // From 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $programme->label(),
    );

    // From 'title' to 'title'.
    $expected = [
      [
        'string' => 'Example Degree Programme',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['title'], 'title');

    // From 'field_programme_code' to 'code'.
    $expected = [['value' => 'PROG-1']];
    $this->assertEquals($expected, $values['code'], 'code');

    // From 'field_credits' to 'programme__ects'.
    $expected = [['value' => 180]];
    $this->assertEquals($expected, $values['programme__ects'], 'programme__ects');

    // From 'field_eqf_level' to 'programme__eqf_level_provided'.
    $expected = [['value' => 6]];
    $this->assertEquals($expected, $values['programme__eqf_level_provided'], 'programme__eqf_level_provided');

    // From 'field_programme_abbreviation' to 'programme__abbreviation'.
    $expected = [
      [
        'string' => 'Example Programme',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['programme__abbreviation'], 'programme__abbreviation');

    // From 'field_programme_description' to 'description'.
    $expected = [
      [
        'multiline' => 'Description of this Programme.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['description'], 'description');

    // From 'field_learning_opportunity_type' to 'programme__elm_lo_type'.
    $expected = [['value' => '79343569f3']];
    $this->assertEquals($expected, $values['programme__elm_lo_type'], 'programme__elm_lo_type');

    // From 'field_programme_mode_of_study' to 'programme__elm_learning_schedule'.
    $expected = [['value' => '72a0ab92fa']];
    $this->assertEquals($expected, $values['programme__elm_learning_schedule'], 'programme__elm_learning_schedule');

    // From 'field_programme_mode_of_learning' to 'programme__elm_mode_of_learning'.
    $expected = [
      ['value' => '9191af2ed9'],
      ['value' => '920fbb3cbe'],
    ];
    $this->assertEquals($expected, $values['programme__elm_mode_of_learning'], 'programme__elm_mode_of_learning');

    // From 'field_isced_f' to 'programme__isced_code'.
    $expected = [['value' => '0011']];
    $this->assertEquals($expected, $values['programme__isced_code'], 'programme__isced_code');

    // From 'field_programme_language_of_inst' to 'language_of_instruction'.
    $expected = [
      ['lang' => 'en'],
      ['lang' => 'pt-PT'],
    ];
    $this->assertEquals($expected, $values['language_of_instruction'], 'language_of_instruction');

    // From 'field_programme_learn_outcomes' to 'learning_outcomes'.
    $expected = [
      [
        'multiline' => 'Learning outcomes of this Programme.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['learning_outcomes'], 'learning_outcomes');

    // From 'field_length_of_programme' and 'field_number_of_terms' to 'programme__length'.
    $expected = [['value' => '6/2']];
    $this->assertEquals($expected, $values['programme__length'], 'programme__length');

    // From 'field_programme_web' to 'url'.
    $expected = [
      [
        'uri' => 'https://example.com/programme/1',
        'title' => 'example.com/programme/1',
        'options' => [],
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['url'], 'url');

    // From 'field_programme_start_date' to 'programme__valid_since'.
    $expected = [['value' => '2020-01-01']];
    $this->assertEquals($expected, $values['programme__valid_since'], 'programme__valid_since');

    // From 'field_programme_end_date' to 'programme__valid_until'.
    $expected = [['value' => '2030-12-31']];
    $this->assertEquals($expected, $values['programme__valid_until'], 'programme__valid_until');

    // From Group reference to 'hei'.
    $expected = [['target_id' => '1']];
    $this->assertEquals($expected, $values['hei'], 'hei');

    // From source UUID to 'source_uuid'.
    $source_uuid = $programme->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);
  }

}
