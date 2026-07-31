<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\EntityManager;

/**
 * Defines a GroupInstitution data transformer.
 */
class GroupInstitution extends DataTransformerBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\dacem_sync\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs data transformer.
   *
   * @param \Drupal\dacem_sync\EntityManager $entity_manager
   *   The entity manager.
   */
  public function __construct(
    EntityManager $entity_manager,
  ) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return 'group_hei';
  }

  /**
   * {@inheritdoc}
   */
  public function doTransform(ContentEntityInterface $source, array $strategy): array {
    $output = [
      ['target_id' => (string) $this->entityManager->getGroupHeiId($source)],
    ];

    return $output;
  }

}
