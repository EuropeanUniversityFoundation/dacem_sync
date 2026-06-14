<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync\Plugin\QueueWorker\DacemSyncQueueWorker;
use Drupal\dacem_sync_occ_entities\SyncHandler\CourseSyncHandler;
use Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler;
use Drupal\node\Entity\Node;
use Drupal\occ_entities\Entity\LearningOpportunitySpecification;

/**
 * Tests CourseSyncHandler.
 *
 * @group dacem_sync
 */
class CourseSyncHandlerTest extends CourseSyncHandlerTestBase {

  /**
   * The sync handler.
   *
   * @var \Drupal\dacem_sync_occ_entities\SyncHandler\CourseSyncHandler
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

    // Create a Node of type Programme.
    $programme = Node::create([
      'type' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'title' => 'Example Degree Programme',
      'field_programme_code' => 'PROG-1',
      'field_credits' => 180,
      'field_eqf_level' => 6,
      'field_programme_description' => 'Description of this Programme.',
      // Basic programmes and qualifications.
      'field_isced_f' => '0011',
      'field_programme_language_of_inst' => [
      // English.
        1,
      // Portuguese (Portugal).
        2,
      ],
      'field_programme_learn_outcomes' => 'Learning outcomes of this Programme.',
      'field_length_of_programme' => 12,
      'field_number_of_terms' => 4,
      'status' => 1,
    ]);
    $programme->save();

    // Add a Relationship between the Group and the Programme.
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
        'entity_id' => $programme->id(),
      ]);

    $relationship->save();

    // Create a Node of type Individual Educational Component.
    $node = Node::create([
      'type' => CourseSyncHandler::SOURCE_BUNDLE,
      'title' => 'Example Course',
      'field_iec_code' => 'CR-1',
      'field_credits' => 6,
      'field_iec_term' => [1, 3],
      'field_iec_programme' => $programme->id(),
      'field_iec_description' => 'Description of this Course.',
    // Written examination.
      'field_assessment_method_types' => '6e6cb2cc78',
    // Classroom coursework.
      'field_iec_activity_types' => 'ff436ea7c9',
    // Course.
      'field_iec_elm_type' => '05053c1cbe',
      'field_iec_modality' => [
    // Presential.
        '9191af2ed9',
    // Online.
        '920fbb3cbe',
      ],
      // Basic courses and qualifications.
      'field_fields_of_study' => '0011',
      'field_iec_language_of_instructio' => [
        // Portuguese (Portugal).
        2,
      // English.
        1,
      ],
      'field_iec_learning_outcomes' => 'Learning outcomes of this Course.',
      'field_iec_avaliable_for_mobility' => TRUE,
      'field_iec_restricted_alliance' => FALSE,
      'field_iec_web' => [
        'uri' => 'https://example.com/course/1',
        'title' => 'example.com/course/1',
      ],
      'field_iec_recommendations' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'field_iec_contents' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'field_iec_requirements' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'field_iec_planned_activities' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'field_iec_evaluation' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'field_iec_coordinator' => 'Anonymous',
      'status' => 1,
    ]);
    $node->save();

    // Add a Relationship between the Group and the Node.
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
    $values = $course->toArray();

    // 'title' to 'label'.
    $this->assertEquals(
      $node->label(),
      $course->label(),
    );

    // 'title' to 'title'.
    $expected = [
      [
        'string' => 'Example Course',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['title']);

    // 'field_iec_code' to 'code'.
    $expected = [['value' => 'CR-1']];
    $this->assertEquals($expected, $values['code']);

    // 'field_credits' to 'course__ects'.
    $expected = [['value' => 6]];
    $this->assertEquals($expected, $values['course__ects']);

    // 'field_iec_term' to 'course__academic_term'.
    // 'field_iec_programme.field_number_of_terms' to 'course__academic_term'.
    $expected = [
      ['value' => '1/4'],
      ['value' => '3/4'],
    ];
    $this->assertEquals($expected, $values['course__academic_term']);

    // 'field_course_description' to 'description'.
    $expected = [
      [
        'multiline' => 'Description of this Course.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['description']);

    // 'field_assessment_method_types' to 'course__elm_assessment_type'.
    $expected = [['value' => '6e6cb2cc78']];
    $this->assertEquals($expected, $values['course__elm_assessment_type']);

    // 'field_iec_activity_types' to 'course__elm_activity_type'.
    $expected = [['value' => 'ff436ea7c9']];
    $this->assertEquals($expected, $values['course__elm_activity_type']);

    // 'field_iec_elm_type' to 'course__elm_lo_type'.
    $expected = [['value' => '05053c1cbe']];
    $this->assertEquals($expected, $values['course__elm_lo_type']);

    // 'field_iec_modality' to 'course__elm_mode_of_learning'.
    $expected = [
      ['value' => '9191af2ed9'],
      ['value' => '920fbb3cbe'],
    ];
    $this->assertEquals($expected, $values['course__elm_mode_of_learning']);

    // 'field_fields_of_study' to 'course__isced_code'.
    $expected = [['value' => '0011']];
    $this->assertEquals($expected, $values['course__isced_code']);

    // 'field_iec_language_of_instructio' to 'language_of_instruction'.
    $expected = [
      ['lang' => 'pt-PT'],
      ['lang' => 'en'],
    ];
    $this->assertEquals($expected, $values['language_of_instruction']);

    // 'field_iec_learning_outcomes' to 'learning_outcomes'.
    $expected = [
      [
        'multiline' => 'Learning outcomes of this Course.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['learning_outcomes']);

    // 'field_iec_avaliable_for_mobility' to 'course__restricted_to_local'.
    $expected = [['value' => '0']];
    $this->assertEquals($expected, $values['course__restricted_to_local']);

    // 'field_iec_restricted_alliance' to 'course__restricted_to_alliance'.
    $expected = [['value' => '0']];
    $this->assertEquals($expected, $values['course__restricted_to_alliance']);

    // 'field_iec_web' to 'url'.
    $expected = [
      [
        'uri' => 'https://example.com/course/1',
        'title' => 'example.com/course/1',
        'options' => [],
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['url']);

    // 'field_iec_recommendations' to 'course__bibliography'.
    $expected = [
      [
        'multiline' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['course__bibliography']);

    // 'field_iec_contents' to 'course__course_content'.
    $expected = [
      [
        'multiline' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['course__course_content']);

    // 'field_iec_requirements' to 'course__prerequisites'.
    $expected = [
      [
        'multiline' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['course__prerequisites']);

    // 'field_iec_planned_activities' to 'course__teaching_method'.
    $expected = [
      [
        'multiline' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['course__teaching_method']);

    // 'field_iec_evaluation' to 'course__assessment_method'.
    $expected = [
      [
        'multiline' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'lang' => 'en',
      ],
    ];
    $this->assertEquals($expected, $values['course__assessment_method']);

    // From Group reference to 'hei'.
    $expected = [['target_id' => '1']];
    $this->assertEquals($expected, $values['hei'], 'hei');

    // From source UUID to 'source_uuid'.
    $source_uuid = $course->get(EntityManager::BASE_FIELD)
      ->getValue()[0]['value'];

    $this->assertEquals($node->uuid(), $source_uuid, EntityManager::BASE_FIELD);
  }

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

  /**
   * Tests the onDelete workflow.
   */
  public function testOnDelete(): void {
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
  }

}
