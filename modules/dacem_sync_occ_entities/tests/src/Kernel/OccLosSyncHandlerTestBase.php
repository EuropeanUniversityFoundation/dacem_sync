<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync_occ_entities\SyncHandler\CourseSyncHandler;
use Drupal\dacem_sync_occ_entities\SyncHandler\OccLosSyncHandlerBase;
use Drupal\dacem_sync_occ_entities\SyncHandler\ProgrammeSyncHandler;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\group\Entity\GroupRelationshipType;
use Drupal\group\Entity\GroupType;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Setup for testing classes extending OccLosSyncHandlerBase.
 *
 * @group dacem_sync
 */
class OccLosSyncHandlerTestBase extends KernelTestBase {

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
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('field_storage_config');
    $this->installEntitySchema('field_config');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_relationship');
    $this->installEntitySchema('group_relationship_type');
    $this->installEntitySchema('contact');
    $this->installEntitySchema('hei');
    $this->installEntitySchema('ounit');
    $this->installEntitySchema('occ_los');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('node', [
      'node_access',
    ]);

    // Define a Programme content type.
    $node_type = NodeType::create([
      'type' => ProgrammeSyncHandler::SOURCE_BUNDLE,
      'name' => 'Programme',
    ]);
    $node_type->save();

    // Define an Individual Educational Component content type.
    $node_type = NodeType::create([
      'type' => CourseSyncHandler::SOURCE_BUNDLE,
      'name' => 'Individual Educational Component',
    ]);
    $node_type->save();

    $this->installConfig([
      'field',
      'filter',
      'node',
      'group',
      'gnode',
      'language',
      'content_translation',
      'ewp_core',
      'ewp_contact',
      'ewp_institutions',
      'elm_vocabulary_field',
      'occ_entities',
    ]);

    // Create a group type with an Institution reference field.
    $group_type = GroupType::create([
      'id' => EntityManager::GROUP_TYPE_ID,
      'label' => 'Example group type',
    ]);
    $group_type->save();

    $los_bundles = [
      CourseSyncHandler::SOURCE_BUNDLE,
      ProgrammeSyncHandler::SOURCE_BUNDLE,
    ];

    // Create the field storage for Groups.
    $field_storage = FieldStorageConfig::create([
      'field_name' => EntityManager::GROUP_HEI_REF,
      'entity_type' => 'group',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'hei',
      ],
    ]);
    $field_storage->save();

    // Create the field config for the Group type.
    $field_instance = FieldConfig::create([
      'field_name' => EntityManager::GROUP_HEI_REF,
      'entity_type' => 'group',
      'bundle' => EntityManager::GROUP_TYPE_ID,
      'label' => 'Institution Reference',
      'settings' => [
        'handler' => 'default:hei',
        'handler_settings' => [],
      ],
    ]);
    $field_instance->save();

    // Prepare Group Relationship types for all OCC bundles.
    foreach ($los_bundles as $los_bundle) {
      $plugin_id = implode(':', ['group_node', $los_bundle]);
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

      // Create a Group Relationship type for each bundle.
      $relationship_type = GroupRelationshipType::create([
        'id' => $safe_id,
        'group_type' => EntityManager::GROUP_TYPE_ID,
        'label' => sprintf('Content Relationship for %s entities', $los_bundle),
        'content_plugin' => $plugin_id,
      ]);
      $relationship_type->save();
    }

    // Add a language for content translation.
    ConfigurableLanguage::createFromLangcode('pt-pt')->save();

    // Create the Vocabulary for languages.
    $vocabulary = Vocabulary::create([
      'vid' => 'languages_of_instuction',
      'name' => 'Languages of Instuction',
    ]);
    $vocabulary->save();

    // Create the field storage for Taxonomy Terms.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_lang',
      'entity_type' => 'taxonomy_term',
      'type' => 'ewp_lang',
      'cardinality' => 1,
    ]);
    $field_storage->save();

    // Create the field config for the relevant vocabulary.
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => OccLosSyncHandlerBase::LANG_VOCABULARY,
      'label' => 'Language tag',
      'required' => FALSE,
    ]);
    $field_instance->save();

    // Create terms with language tags.
    $term = Term::create([
      'vid' => OccLosSyncHandlerBase::LANG_VOCABULARY,
      'name' => 'English',
      'field_lang' => 'en',
    ]);
    $term->save();

    $term = Term::create([
      'vid' => OccLosSyncHandlerBase::LANG_VOCABULARY,
      'name' => 'Portuguese (Portugal)',
      'field_lang' => 'pt-PT',
    ]);
    $term->save();
  }

}
