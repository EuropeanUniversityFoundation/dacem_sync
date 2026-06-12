<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync_occ_entities\SyncHandler\CourseSyncHandler;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Setup for testing CourseSyncHandler.
 *
 * @group dacem_sync
 */
class CourseSyncHandlerTestBase extends OccLosSyncHandlerTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_code',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course code',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_credits',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Number of credits',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_term',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'integer',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Academic term',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_programme',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'node',
      ],
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Programme',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_description',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course description',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_assessment_method_types',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'elm_controlled_vocabulary',
      'cardinality' => -1,
      'settings' => [
        'vocabulary' => 'assessment',
      ],
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course ELM:AT',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_activity_types',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'elm_controlled_vocabulary',
      'cardinality' => 1,
      'settings' => [
        'vocabulary' => 'learning_activity',
      ],
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course ELM:LAT',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_elm_type',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
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
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course ELM:LOT',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_modality',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
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
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course ELM:MLA',
      'required' => FALSE,
    ]);

    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_fields_of_study',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'isced_f',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'ISCED-F field of study',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_language_of_instructio',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
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
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Language of instruction',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_learning_outcomes',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Learning outcomes',
      'required' => TRUE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_avaliable_for_mobility',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'boolean',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Available for mobility',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_restricted_alliance',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'boolean',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Restricted to alliance students',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_web',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'link',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course website',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_recommendations',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Bibliography',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_contents',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Course content',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_requirements',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Prerequisites',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_planned_activities',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Teaching method',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_evaluation',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'text_long',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Assessment method',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create the field storage for Content.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_iec_coordinator',
      'entity_type' => CourseSyncHandler::SOURCE_ENTITY_TYPE_ID,
      'type' => 'string',
      'cardinality' => -1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant Content type.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => CourseSyncHandler::SOURCE_BUNDLE,
      'label' => 'Coordinator',
      'required' => FALSE,
    ]);
    $field_instance->save();

  }

}
