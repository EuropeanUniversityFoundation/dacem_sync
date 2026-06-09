<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_ewp_ounits\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync_ewp_ounits\SyncHandler\OunitSyncHandler;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\group\Entity\GroupRelationshipType;
use Drupal\group\Entity\GroupType;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;

/**
 * Setup for testing OunitSyncHandler.
 *
 * @group dacem_sync
 */
class OunitSyncHandlerTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'action',
    'user',
    'node',
    'group',
    'field',
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
    'dacem_sync',
    'dacem_sync_ewp_ounits',
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
    $this->installSchema('node', [
      'node_access',
    ]);

    // Define an Organizational unit content type.
    $node_type = NodeType::create([
      'type' => OunitSyncHandler::SOURCE_BUNDLE,
      'name' => 'Organizational Unit',
    ]);
    $node_type->save();

    $this->installConfig([
      'node',
      'group',
      'gnode',
      'language',
      'content_translation',
      'ewp_core',
      'ewp_contact',
      'ewp_institutions',
      'ewp_ounits',
    ]);

    // Define a group type with an Institution reference field.
    $group_type = GroupType::create([
      'id' => EntityManager::GROUP_TYPE_ID,
      'label' => 'Example group type',
    ]);
    $group_type->save();

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

    $field_storage = FieldStorageConfig::create([
      'field_name' => EntityManager::GROUP_HEI_REF,
      'entity_type' => 'group',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'hei',
      ],
    ]);
    $field_storage->save();

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

    // This field will not be syncronized.
    FieldStorageConfig::create([
      'field_name' => 'field_ou_description',
      'entity_type' => 'node',
      'type' => 'text_long',
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_ou_description',
      'entity_type' => 'node',
      'bundle' => OunitSyncHandler::SOURCE_BUNDLE,
      'cardinality' => 1,
    ])->save();

    // Add a language for content translation.
    ConfigurableLanguage::createFromLangcode('pt-pt')->save();
  }

}
