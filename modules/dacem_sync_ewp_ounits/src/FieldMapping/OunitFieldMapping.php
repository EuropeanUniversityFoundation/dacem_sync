<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_ewp_ounits\FieldMapping;

use Drupal\dacem_sync\FieldMappingInterface;

/**
 * Maps 'ounit' from 'organizational_unit'.
 */
class OunitFieldMapping implements FieldMappingInterface {

  /**
   * {@inheritdoc}
   */
  public function mapping(): array {
    return [
      'ounit' => [
        'ounit' => [
          'label' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'title',
            'transformer' => 'canonical',
          ],
          'name' => [
            'properties' => [
              'string' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'title',
            'transformer' => 'multilingual',
          ],
          'abbreviation' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_ou_abbreviation',
            'transformer' => 'canonical',
          ],
          'ounit_id' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'uuid',
            'transformer' => 'canonical',
          ],
          'ounit_code' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_ou_code',
            'transformer' => 'canonical',
          ],
          'website_url' => [
            'properties' => [
              'uri' => 'uri',
              'title' => 'title',
              'options' => 'options',
              'lang' => 'langcode',
            ],
            'source' => 'field_ou_web',
            'transformer' => 'multilingual',
          ],
          'parent_hei' => [
            'required' => TRUE,
            'transformer' => 'group_hei',
          ],
        ],
      ],
    ];
  }

}
