<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_entity_date_range_test\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface;
use Drupal\omnipedia_date\Entity\EntityWithDateRangeTrait;

/**
 * Defines the TestEntityWithDateRange entity.
 *
 * @ContentEntityType(
 *   id           = "test_entity_with_date_range",
 *   label        = @Translation("Test entity with date range"),
 *   base_table   = "test_entity_with_date_range",
 *   entity_keys  = {
 *     "id"   = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class TestEntityWithDateRange extends ContentEntityBase implements EntityWithDateRangeInterface {

  use EntityWithDateRangeTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {

    return [
      'id'  => BaseFieldDefinition::create('integer')
        ->setLabel(new TranslatableMarkup('ID'))
        ->setDescription(new TranslatableMarkup(
          'The ID of the TestEntityWithDateRange entity.',
        ))
        ->setReadOnly(true),

      'uuid'  => BaseFieldDefinition::create('uuid')
        ->setLabel(new TranslatableMarkup('UUID'))
        ->setDescription(new TranslatableMarkup(
          'The UUID of the TestEntityWithDateRange entity.',
        ))
        ->setReadOnly(true),

      'date_range' => static::dateRangeBaseFieldDefinition($entityType)
        ->setDisplayOptions('form', [
          'weight'    => -3,
        ])
        ->setDisplayOptions('view', [
          'label'   => 'above',
          'weight'  => -3,
        ]),
    ];

  }

}
