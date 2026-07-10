<?php

declare(strict_types=1);

namespace Drupal\dacem_sync_occ_entities\FieldMapping;

use Drupal\dacem_sync\DataTransformer\ReferencedEntityField;
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
            'source' => 'field_iec_code',
            'transformer' => 'canonical',
          ],
          'course__ects' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_credits',
            'transformer' => 'int_to_float',
          ],
          'course__academic_term' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => [
              'field_iec_term',
              'field_iec_programme.occ_los.field_number_of_terms',
            ],
            'transformer' => 'academic_term',
          ],
          'description' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'field_iec_description',
            'transformer' => 'multilingual',
          ],
          'course__elm_assessment_type' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_assessment_method_types',
            'transformer' => 'canonical',
          ],
          'course__elm_activity_type' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_iec_activity_types',
            'transformer' => 'canonical',
          ],
          'course__elm_lo_type' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_iec_elm_type',
            'transformer' => 'canonical',
          ],
          'course__elm_mode_of_learning' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_iec_modality',
            'transformer' => 'canonical',
          ],
          'course__isced_code' => [
            'properties' => [
              'value' => 'value',
            ],
            'required' => TRUE,
            'source' => 'field_fields_of_study',
            'transformer' => 'canonical',
          ],
          'language_of_instruction' => [
            'properties' => [
              'lang' => 'lang',
            ],
            'required' => TRUE,
            'source' => implode(ReferencedEntityField::GLUE, [
              'field_iec_language_of_instructio',
              'term',
              'field_lang',
            ]),
            'transformer' => 'referenced_entity_field',
          ],
          'learning_outcomes' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'required' => TRUE,
            'source' => 'field_iec_learning_outcomes',
            'transformer' => 'multilingual',
          ],
          'course__restricted_to_local' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_iec_avaliable_for_mobility',
            'transformer' => 'negate',
          ],
          'course__restricted_to_alliance' => [
            'properties' => [
              'value' => 'value',
            ],
            'source' => 'field_iec_restricted_alliance',
            'transformer' => 'canonical',
          ],
          'url' => [
            'properties' => [
              'uri' => 'uri',
              'title' => 'title',
              'options' => 'options',
              'lang' => 'langcode',
            ],
            'source' => 'field_iec_web',
            'transformer' => 'multilingual',
          ],
          'course__bibliography' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'source' => 'field_iec_recommendations',
            'transformer' => 'multilingual',
          ],
          'course__course_content' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'source' => 'field_iec_contents',
            'transformer' => 'multilingual',
          ],
          'course__prerequisites' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'source' => 'field_iec_requirements',
            'transformer' => 'multilingual',
          ],
          'course__teaching_method' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'source' => 'field_iec_planned_activities',
            'transformer' => 'multilingual',
          ],
          'course__assessment_method' => [
            'properties' => [
              'multiline' => 'value',
              'lang' => 'langcode',
            ],
            'source' => 'field_iec_evaluation',
            'transformer' => 'multilingual',
          ],
          'hei' => [
            'required' => TRUE,
            'transformer' => 'group_hei',
          ],
          'course__related_programme' => [
            'source' => [
              'field_iec_programme',
              'field_iec_type',
              'field_iec_year',
              'field_iec_programme.occ_los.field_length_of_programme',
              'field_iec_programme.occ_los.field_number_of_terms',
            ],
            'transformer' => 'related_programme',
          ],
        ],
      ],
    ];
  }

}
