<?php

declare(strict_types=1);

namespace Drupal\dacem_sync\DataTransformer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dacem_sync\DataTransformerInterface;
use Drupal\dacem_sync\EntityManager;

/**
 * Defines a GroupInstitution data transformer.
 */
class GroupInstitution implements DataTransformerInterface {

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
   *   The entity type manager.
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
  public function transform(ContentEntityInterface $source, array $strategy): array {
    $output = [
      ['target_id' => $this->entityManager->getGroupHeiId($source)],
    ];

    return $output;
  }

}
