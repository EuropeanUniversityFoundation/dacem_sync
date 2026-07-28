<?php

declare(strict_types=1);

namespace Drupal\Tests\dacem_sync_occ_entities\Unit\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\dacem_sync\EntityManager;
use Drupal\dacem_sync_occ_entities\DataTransformer\Ounit;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Ounit data transformer.
 *
 * @group dacem_sync
 */
class OunitTest extends UnitTestCase {

  public const UUID = '01234567-89ab-cdef-0123-456789abcdef';

  /**
   * Tests that the data transformer can mirror references.
   */
  public function testTransformerMirrorsReferences(): void {
    $ounit_ewp = $this->createMock(ContentEntityInterface::class);
    $ounit_ewp->method('id')->willReturn(202);

    $ounit_node = $this->createMock(ContentEntityInterface::class);
    $ounit_node->method('uuid')->willReturn(self::UUID);

    $reference = $this->createMock(EntityReferenceFieldItemListInterface::class);
    $reference->method('referencedEntities')->willReturn([$ounit_node]);

    $entity = $this->createMock(ContentEntityInterface::class);
    $entity->method('get')->with('field_programme_ou')->willReturn($reference);

    $strategy = [
      'properties' => [
        'target_id' => 'target_id',
      ],
      'source' => 'field_programme_ou',
      'transformer' => 'ounit',
    ];

    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->method('loadBySourceUuid')
      ->with('ounit', self::UUID)
      ->willReturn($ounit_ewp);

    $transformer = new Ounit($entity_manager);

    $result = $transformer->transform(
      $entity,
      $strategy
    );

    $this->assertSame(
      [
        [
          'target_id' => '202',
        ],
      ],
      $result
    );
  }

}
