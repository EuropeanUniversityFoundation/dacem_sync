<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\FieldMapping;

use Drupal\dacem_sync\FieldMappingInterface;

/**
 * Defines a field mapping for 'ounit' from 'organizational_unit'.
 */
class OunitFieldMapping implements FieldMappingInterface {

  /**
   * {@inheritdoc}
   */
  public function mapping(): array {
    return [
      'ounit' => [
        'ounit' => [
          'title' => [
            'properties' => [
              'string' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'title',
            'transform' => 'multilingual',
          ],
          'abbreviation' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_ou_abbreviation',
            'transform' => 'canonical',
          ],
          'ounit_id' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'uuid',
            'transform' => 'canonical',
          ],
          'ounit_code' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_ou_code',
            'transform' => 'canonical',
          ],
          'url' => [
            'properties' => [
              'uri' => 'uri',
              'title' => 'title',
              'options' => 'options',
              'lang' => 'langcode',
            ],
            'source' => 'field_ou_web',
            'transform' => 'multilingual',
          ],
          'parent_hei' => [
            'required' => TRUE,
            'transform' => 'group_hei',
          ],
        ],
      ],
    ];
  }

}
