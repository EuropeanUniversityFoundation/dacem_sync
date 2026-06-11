<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\FieldMapping;

use Drupal\dacem_sync\FieldMappingInterface;

/**
 * Maps 'occ_los:programme' from 'programme'.
 */
class ProgrammeFieldMapping implements FieldMappingInterface {

  /**
   * {@inheritdoc}
   */
  public function mapping(): array {
    return [
      'occ_los' => [
        'programme' => [
          'url' => [
            'properties' => [
              'uri' => 'uri',
              'title' => 'title',
              'options' => 'options',
              'lang' => 'langcode',
            ],
            'source' => '',
            'transformer' => 'multilingual',
          ],
          'hei' => [
            'required' => TRUE,
            'transformer' => 'group_hei',
          ],
        ],
      ],
    ];
  }

}
