<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\SyncHandler;

use Drupal\dacem_sync\SyncHandlerInterface;

/**
 * Defines a base sync handler for OCC Entities.
 */
abstract class OccLosSyncHandlerBase implements SyncHandlerInterface {

  public const TARGET_ENTITY_TYPE_ID = 'occ_los';
  public const TARGET_UNIQUE_PER_HEI = 'code';
  public const TARGET_HEI_FIELD = 'hei';

  public const LANG_VOCABULARY = 'languages_of_instuction';

}
