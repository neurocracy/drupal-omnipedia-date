<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Field\FieldType;

/**
 * Interface for Omnipedia date range field items.
 */
interface OmnipediaDateRangeItemInterface {

  /**
   * Get this field items's start date.
   *
   * @return string
   *   Either the stored date string or the string 'first' if not set.
   */
  public function getStartDate(): string;

  /**
   * Get this field items's end date.
   *
   * @return string
   *   Either the stored date string or the string 'last' if not set.
   */
  public function getEndDate(): string;


}
