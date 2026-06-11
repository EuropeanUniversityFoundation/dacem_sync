<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\FieldMapping;

use Drupal\dacem_sync\FieldMappingInterface;

/**
 * Maps 'occ_los:course' from 'individual_educational_component'.
 */
class CourseFieldMapping implements FieldMappingInterface {

  /**
   * {@inheritdoc}
   */
  public function mapping(): array {
    return [
      'occ_los' => [
        'course' => [
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
