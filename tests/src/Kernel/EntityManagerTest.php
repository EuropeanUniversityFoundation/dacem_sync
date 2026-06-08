<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync\Kernel;

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Tests the entity manager service.
 *
 * @group dacem_sync
 */
class EntityManagerTest extends EntityManagerTestBase {

  /**
   * Tests the buildFromProperties() method.
   */
  public function testBuildFromProperties(): void {
    $manager = $this->container->get('dacem_sync.entity_manager');

    $user = $manager->buildFromProperties('user', [
      // Bundle is ommitted by the EntityBuilder.
      'name' => 'Test user',
    ]);

    $this->assertInstanceOf(UserInterface::class, $user);
    $this->assertEquals('user', $user->getEntityTypeId());
    $this->assertEquals('user', $user->bundle());
    $this->assertEquals('Test user', $user->getAccountName());

    $page = $manager->buildFromProperties('node', [
      // The bundle key for node is 'type'.
      'bundle_key' => 'page',
      'title' => 'Test page',
    ]);

    $this->assertInstanceOf(NodeInterface::class, $page);
    $this->assertEquals('node', $page->getEntityTypeId());
    $this->assertEquals('page', $page->bundle());
    $this->assertEquals('Test page', $page->getTitle());

    $image = $manager->buildFromProperties('media', [
      // The bundle key for media is 'bundle'.
      'bundle_key' => 'image',
      'name' => 'Test image',
    ]);

    $this->assertInstanceOf(MediaInterface::class, $image);
    $this->assertEquals('media', $image->getEntityTypeId());
    $this->assertEquals('image', $image->bundle());
    $this->assertEquals('Test image', $image->getName());
  }

}
