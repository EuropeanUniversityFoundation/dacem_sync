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
          'title' => [
            'properties' => [
              'string' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'title',
            'transformer' => 'multilingual',
          ],
          'code' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_programme_code',
            'transformer' => 'canonical',
          ],
          'ects' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_credits',
            'transformer' => 'int_to_float',
          ],
          'eqf_level_provided' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_eqf_level',
            'transformer' => 'canonical',
          ],
          'abbreviation' => [
            'properties' => [
              'string' => 'value',
              'lang' => 'langcode',
            ],
            'source' => 'field_programme_abbreviation',
            'transformer' => 'multilingual',
          ],
          'description' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'field_programme_description',
            'transformer' => 'multilingual',
          ],
          'programme__elm_lo_type' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_learning_opportunity_type',
            'transformer' => 'canonical',
          ],
          'programme__elm_learning_schedule' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_programme_mode_of_study',
            'transformer' => 'canonical',
          ],
          'programme__elm_mode_of_learning' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_programme_mode_of_learning',
            'transformer' => 'canonical',
          ],
          'programme__isced_code' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_isced_f',
            'transformer' => 'canonical',
          ],
          'language_of_instruction' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_programme_language_of_inst.term.field_UNKNOWN',
            'transformer' => 'referenced_entity_field',
          ],
          'learning_outcomes' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'field_programme_learn_outcomes',
            'transformer' => 'multilingual',
          ],
          'programme__length' => [
            'properties' => [
              'value' => 'concat',
            ],
            'required' => TRUE,
            'source' => [
              'field_length_of_programme',
              'field_number_of_terms',
            ],
            'transformer' => 'fraction',
          ],
          'url' => [
            'properties' => [
              'uri' => 'uri',
              'title' => 'title',
              'options' => 'options',
              'lang' => 'langcode',
            ],
            'source' => 'field_programme_web',
            'transformer' => 'multilingual',
          ],
          'programme__valid_since' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_programme_start_date',
            'transformer' => 'canonical',
          ],
          'programme__valid_until' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_programme_end_date',
            'transformer' => 'canonical',
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
