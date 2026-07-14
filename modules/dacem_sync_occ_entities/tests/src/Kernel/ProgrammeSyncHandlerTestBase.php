<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

/**
 * Setup for testing ProgrammeSyncHandler.
 *
 * @group dacem_sync
 */
class ProgrammeSyncHandlerTestBase extends OccLosSyncHandlerTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Code.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_code',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Code',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Abbreviation.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_abbreviation',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Abbreviation',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Fields of study.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_isced_f',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'isced_f',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Fields of study',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Learning opportunity type.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_learning_opportunity_type',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'elm_controlled_vocabulary',
      'cardinality' => -1,
      'settings' => [
        'vocabulary' => 'learning_opportunity',
      ],
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Learning opportunity type',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Level of qualification.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_eqf_level',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Level of qualification',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Mode of learning.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_mode_of_learning',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'elm_controlled_vocabulary',
      'cardinality' => -1,
      'settings' => [
        'vocabulary' => 'learning_assessment',
      ],
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Mode of learning',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Length of programme.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_length_of_programme',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Length of programme',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Number of terms per year.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_number_of_terms',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Number of terms per year',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Number of credits.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_credits',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Number of credits',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Mode of study.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_mode_of_study',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'elm_controlled_vocabulary',
      'cardinality' => 1,
      'settings' => [
        'vocabulary' => 'learning_schedule',
      ],
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Mode of study',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Languages of instruction.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_language_of_inst',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
      'cardinality' => -1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Languages of instruction',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Start date.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_start_date',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Start date',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // End date.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_end_date',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'End date',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Webpage.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_web',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'link',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Webpage',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Description.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_description',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Description',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Learning outcomes.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_learn_outcomes',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Learning outcomes',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Mobility (not mapped).
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_mobility',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Mobility',
      'required' => FALSE,
    ]);
    $field_instance->save();

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
      'field_programme_abbreviation' => 'Example Programme',
      // Basic programmes and qualifications.
      'field_isced_f' => '0011',
      // Educational programme.
      'field_learning_opportunity_type' => '79343569f3',
      'field_eqf_level' => 6,
      'field_programme_mode_of_learning' => [
        // Presential.
        '9191af2ed9',
        // Online.
        '920fbb3cbe',
      ],
      'field_length_of_programme' => 6,
      'field_number_of_terms' => 2,
      'field_credits' => 180,
      // Full time.
      'field_programme_mode_of_study' => '72a0ab92fa',
      'field_programme_language_of_inst' => [
        // English.
        1,
        // Portuguese (Portugal).
        2,
      ],
      'field_programme_start_date' => '2020-01-01',
      'field_programme_end_date' => '2030-12-31',
      'field_programme_web' => [
        'uri' => 'https://example.com/programme/1',
        'title' => 'example.com/programme/1',
      ],
      'field_programme_description' => 'Description of this Programme.',
      'field_programme_learn_outcomes' => 'Learning outcomes of this Programme.',
      'status' => 1,
    ]);
    $programme->save();

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
        'entity_id' => $programme->id(),
      ]);

    $relationship->save();

  }

}
