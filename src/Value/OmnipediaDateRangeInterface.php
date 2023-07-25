<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Value;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Omnipedia date range value object interface.
 *
 * Note that this specifically uses DateTimePlus and not DrupalDateTime as types
 * because the former can be easily unit tested while the latter would require
 * mocking up Drupal's string translation which we don't actually use.
 *
 * @see \Drupal\Component\Datetime\DateTimePlus
 *
 * @see \Drupal\Core\Datetime\DrupalDateTime
 */
interface OmnipediaDateRangeInterface {

  /**
   * Get the start date DateTimePlus object.
   *
   * @return \Drupal\Component\Datetime\DateTimePlus
   */
  public function getStartDate(): DateTimePlus;

  /**
   * Get the end date DateTimePlus object.
   *
   * @return \Drupal\Component\Datetime\DateTimePlus
   */
  public function getEndDate(): DateTimePlus;

  /**
   * Determine if this date range overlaps a date.
   *
   * @param \Drupal\Component\Datetime\DateTimePlus $date
   *   The date object to check against this date range instance.
   *
   * @return bool
   *   True if the provided date falls within the start and end dates of this
   *   date range, inclusive of the start and end dates; false it falls outside
   *   of this date range.
   */
  public function overlapsDate(DateTimePlus $date): bool;

  /**
   * Determine if this date range overlaps with another date range.
   *
   * @param self $dateRange
   *   The date range instance to check against the current instance.
   *
   * @return bool
   *   True if at least one date of the provided date range occurs within the
   *   this date range; false otherwise.
   */
  public function overlapsWithRange(self $dateRange): bool;

}
