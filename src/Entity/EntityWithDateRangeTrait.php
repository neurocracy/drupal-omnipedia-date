<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface;

/**
 * Trait for entities that have a start and end date.
 *
 * @see \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface
 *   Entities using this trait are expected to also implement this interface.
 */
trait EntityWithDateRangeTrait {

  /**
   * Definition for the 'date_range' base field.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition this field is for.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   The created 'date_range' base field.
   *
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   *   Intended to be called from an implementation of this.
   *
   * @see \Drupal\omnipedia_date\Plugin\Field\FieldType\OmnipediaDateRangeItem
   *   Provides this field type.
   */
  protected static function dateRangeBaseFieldDefinition(
    EntityTypeInterface $entityType,
  ): FieldDefinitionInterface {

    return BaseFieldDefinition::create('omnipedia_daterange')
      ->setLabel(new TranslatableMarkup('Date range'))
      ->setDescription(new TranslatableMarkup(
        'The earliest and last dates this @entityLabel refers to. The default start date of "First date" will always refer to the earliest available date. The default end date of "Last date" will always refer to the last available date.',
        ['@entityLabel' => $entityType->getSingularLabel()],
      ))
      // We only use the date without the time of day.
      //
      // @see \Drupal\datetime\Plugin\Field\FieldType\DateTimeItem::defaultStorageSettings()
      ->setSetting('datetime_type', 'date')
      ->addConstraint('NonOverlappingEntityDateRange');

  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate(): string {

    /** @var string|null */
    $value = $this->date_range->value;

    return $value === null ? 'first' : $value;

  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate(): string {

    /** @var string|null */
    $value = $this->date_range->end_value;

    return $value === null ? 'last' : $value;

  }

}
