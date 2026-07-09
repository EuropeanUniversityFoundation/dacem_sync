<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\node\Entity\Node;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests ProgrammeSyncHandler.
 *
 * @group dacem_sync
 */
class ProgrammeSyncHandlerOnInsertTest extends ProgrammeSyncHandlerTestBase {

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

    // 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $programme->label(),
    );

    // 'title' to 'title'.
    $expected = [
      [
        'string' => 'Example Degree Programme',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['title']);

    // 'field_programme_code' to 'code'.
    $expected = [['value' => 'PROG-1']];
    $this->assertEquals($expected, $values['code']);

    // 'field_credits' to 'programme__ects'.
    $expected = [['value' => 180]];
    $this->assertEquals($expected, $values['programme__ects']);

    // 'field_eqf_level' to 'programme__eqf_level_provided'.
    $expected = [['value' => 6]];
    $this->assertEquals($expected, $values['programme__eqf_level_provided']);

    // 'field_programme_abbreviation' to 'programme__abbreviation'.
    $expected = [
      [
        'string' => 'Example Programme',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['programme__abbreviation']);

    // 'field_programme_description' to 'description'.
    $expected = [
      [
        'multiline' => 'Description of this Programme.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['description']);

    // 'field_learning_opportunity_type' to 'programme__elm_lo_type'.
    $expected = [['value' => '79343569f3']];
    $this->assertEquals($expected, $values['programme__elm_lo_type']);

    // 'field_programme_mode_of_study' to 'programme__elm_learning_schedule'.
    $expected = [['value' => '72a0ab92fa']];
    $this->assertEquals($expected, $values['programme__elm_learning_schedule']);

    // 'field_programme_mode_of_learning' to 'programme__elm_mode_of_learning'.
    $expected = [
      ['value' => '9191af2ed9'],
      ['value' => '920fbb3cbe'],
    ];
    $this->assertEquals($expected, $values['programme__elm_mode_of_learning']);

    // 'field_isced_f' to 'programme__isced_code'.
    $expected = [['value' => '0011']];
    $this->assertEquals($expected, $values['programme__isced_code']);

    // 'field_programme_language_of_inst' to 'language_of_instruction'.
    $expected = [
      ['lang' => 'en'],
      ['lang' => 'pt-PT'],
    ];
    $this->assertEquals($expected, $values['language_of_instruction']);

    // 'field_programme_learn_outcomes' to 'learning_outcomes'.
    $expected = [
      [
        'multiline' => 'Learning outcomes of this Programme.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['learning_outcomes']);

    // 'field_length_of_programme' to 'programme__length'.
    // 'field_number_of_terms' to 'programme__length'.
    $expected = [['value' => '6/2']];
    $this->assertEquals($expected, $values['programme__length']);

    // 'field_programme_web' to 'url'.
    $expected = [
      [
        'uri' => 'https://example.com/programme/1',
        'title' => 'example.com/programme/1',
        'options' => [],
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['url']);

    // 'field_programme_start_date' to 'programme__valid_since'.
    $expected = [['value' => '2020-01-01']];
    $this->assertEquals($expected, $values['programme__valid_since']);

    // 'field_programme_end_date' to 'programme__valid_until'.
    $expected = [['value' => '2030-12-31']];
    $this->assertEquals($expected, $values['programme__valid_until']);

    // From Group reference to 'hei'.
    $expected = [['target_id' => '1']];
    $this->assertEquals($expected, $values['hei'], 'hei');

    // From source UUID to 'source_uuid'.
    $source_uuid = $programme->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);
  }

}
