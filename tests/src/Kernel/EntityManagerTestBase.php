<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\MediaType;
use Drupal\node\Entity\NodeType;

/**
 * Setup for EntityManagerTests.
 *
 * @group dacem_sync
 */
class EntityManagerTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'media',
    'image',
    'file',
    'dacem_sync',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    NodeType::create([
      'type' => 'page',
      'name' => 'Basic Page',
    ])->save();

    MediaType::create([
      'id' => 'image',
      'label' => 'Image',
      'source' => 'image',
    ])->save();
  }

}
