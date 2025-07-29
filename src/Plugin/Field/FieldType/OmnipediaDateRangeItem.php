<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\omnipedia_date\Plugin\Field\FieldType\OmnipediaDateRangeItemInterface;

/**
 * Plugin implementation of the 'omnipedia_daterange' field type.
 *
 * This extends the Drupal core 'daterange' field type to make the following
 * changes:
 *
 * - The 'value' and 'end_value' properties are marked as not required, so that
 *   it's possible to set a start date but no end date and vice versa, which is
 *   not possible with the core 'daterange' field type.
 *
 * - Overrides DateRangeItem::isEmpty() to always return false, so that items
 *   with both null start and end dates are still rendered as if they have
 *   meaningful, which in this case they do.
 *
 * - Sets the default widget to 'omnipedia_daterange_datelist' and the default
 *   formatter to 'omnipedia_daterange'.
 *
 * @FieldType(
 *   id                 = "omnipedia_daterange",
 *   label              = @Translation("Date range (Omnipedia)"),
 *   description        = @Translation("Create and store date ranges."),
 *   default_widget     = "omnipedia_daterange_datelist",
 *   default_formatter  = "omnipedia_daterange",
 *   list_class         = "\Drupal\datetime_range\Plugin\Field\FieldType\DateRangeFieldItemList"
 * )
 */
class OmnipediaDateRangeItem extends DateRangeItem implements OmnipediaDateRangeItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(
    FieldStorageDefinitionInterface $fieldDefinition
  ) {
    /** @var array */
    $properties = parent::propertyDefinitions($fieldDefinition);

    $properties['value']->setRequired(false);
    $properties['end_value']->setRequired(false);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate(): string {

    /** @var string|null */
    $value = $this->get('value')->getValue();

    return $value === null ? 'first' : $value;

  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate(): string {

    /** @var string|null */
    $value = $this->get('end_value')->getValue();

    return $value === null ? 'last' : $value;

  }
}
