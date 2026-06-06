<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel;

use Drupal\dacem_sync\EntityManager;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Setup for EntityBuilderTests.
 *
 * @group dacem_sync
 */
class EntityBuilderTestBase extends KernelTestBase {

  /**
   * Source entity.
   */
  protected Node $source;

  /**
   * Target entity.
   */
  protected Node $target;

  /**
   * Field map.
   */
  protected array $map = [
    'title' => [
      'properties' => ['value' => 'value'],
      'source' => 'title',
      'transformer' => 'copy',
    ],
    'field_common' => [
      'properties' => ['value' => 'value'],
      'source' => 'field_common',
      'transformer' => 'copy',
    ],
    'field_multiple' => [
      'properties' => ['value' => 'value'],
      'source' => 'field_multiple',
      'transformer' => 'copy',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'text',
    'action',
    'user',
    'field',
    'node',
    'dacem_sync',
    'dacem_sync_sync_handler_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', [
      'node_access',
    ]);
    $this->installConfig(['node']);

    // Field storage config for content.
    FieldStorageConfig::create([
      'field_name' => 'field_common',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_multiple',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => -1,
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_source_specific',
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();

    FieldStorageConfig::create([
      'field_name' => EntityManager::BASE_FIELD,
      'entity_type' => 'node',
      'type' => 'string',
      'cardinality' => 1,
    ])->save();

    // Example content type with field configs.
    NodeType::create([
      'type' => 'example',
      'name' => 'Example',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_common',
      'entity_type' => 'node',
      'bundle' => 'example',
      'label' => 'Common field',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_multiple',
      'entity_type' => 'node',
      'bundle' => 'example',
      'label' => 'Multiple field',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_source_specific',
      'entity_type' => 'node',
      'bundle' => 'example',
      'label' => 'Source specific field',
    ])->save();

    $this->source = Node::create([
      'type' => 'example',
      'title' => [['value' => 'Example']],
      'field_multiple' => [
        ['value' => 'First of multiple'],
        ['value' => 'Last of multiple'],
      ],
      'field_source_specific' => [['value' => 'Source specific']],
    ]);
    $this->source->save();

    // Clone content type with field configs.
    NodeType::create([
      'type' => 'clone',
      'name' => 'Clone',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_common',
      'entity_type' => 'node',
      'bundle' => 'clone',
      'label' => 'Common field',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_multiple',
      'entity_type' => 'node',
      'bundle' => 'clone',
      'label' => 'Multiple field',
    ])->save();

    FieldConfig::create([
      'field_name' => EntityManager::BASE_FIELD,
      'entity_type' => 'node',
      'bundle' => 'clone',
      'label' => 'Source UUID',
    ])->save();

    $this->target = Node::create([
      'type' => 'clone',
      'title' => [['value' => 'Clone']],
      'field_common' => [['value' => 'Common value']],
      'field_multiple' => [
        ['value' => 'Last of multiple'],
        ['value' => 'First of multiple'],
      ],
      EntityManager::BASE_FIELD => [['value' => $this->source->uuid()]],
    ]);
    $this->target->save();

  }

}
