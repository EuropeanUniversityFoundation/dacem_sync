<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync_occ_entities\SyncHandler\OccLosSyncHandlerBase;
use Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

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

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_code',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme code',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_credits',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Number of credits',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_eqf_level',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'EQF level provided',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_abbreviation',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme abbreviation',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_description',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme description',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
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

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme ELM:LOT',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Create the field storage for Content.
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

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme ELM:LST',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
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

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme ELM:MLA',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_isced_f',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'isced_f',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'ISCED-F field of study',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
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

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Language of instruction',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_learn_outcomes',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Learning outcomes',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_length_of_programme',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Length of programme in number of terms',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_number_of_terms',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Number of terms in a full academic year',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_web',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'link',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme website',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_start_date',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'Start date',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_programme_end_date',
      'entity_type' => ProgrammeSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'label' => 'End date',
      'required' => FALSE,
    ]);
    $field_instance->save();

  }

}
