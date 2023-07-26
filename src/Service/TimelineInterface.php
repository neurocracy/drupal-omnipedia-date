<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Omnipedia timeline service interface.
 */
interface TimelineInterface {

  /**
   * Validate and set the default date.
   *
   * @param string $date
   *   A date string in the storage format.
   */
  public function setDefaultDate(string $date): void;

  /**
   * Get a list of dates that have content.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. Defaults to
   *   false.
   *
   * @return array
   *   Zero or more unique dates that have content. Note that this will likely
   *   vary based on the $includeUnpublished parameter.
   *
   * @see \Drupal\omnipedia_date\Service\DefinedDatesInterface::get()
   *   Wrapper around this.
   */
  public function getDefinedDates(bool $includeUnpublished = false): array;

  /**
   * Get a formatted date.
   *
   * @param string|\Drupal\Component\Datetime\DateTimePlus $dateOrKeyword
   *   Must be one of:
   *
   *   - 'current': Indicates the current date is to be used. This is the
   *     default.
   *
   *   - 'first': Indicates that a localized string representing the first
   *     available date should be returned.
   *
   *   - 'last': Indicates that a localized string representing the last
   *     available date should be returned.
   *
   *   - 'default': Indicates the default date is to be used.
   *
   *   - A string that can be parsed by \Drupal\Component\Datetime\DateTimePlus
   *     without errors.
   *
   *   - An instance of \Drupal\Component\Datetime\DateTimePlus.
   *
   * @param string $format
   *   Keyword of the format to output. Note that this parameter is ignored if
   *   $dateOrKeyword is 'first' or 'last'.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The provided $dateOrKeyword formatted as a string according to $format,
   *   or localized text if $dateOrKeyword is 'first' or 'last'.
   *
   * @see \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface::format()
   *   Lists available $format keywords.
   *
   * @todo Should the 'first' and 'last' options be moved to their own method or
   *   does it make more sense to have them here so that code that calls this
   *   doesn't have to care about whether they're passing a date or a keyword?
   */
  public function getDateFormatted(
    string|DateTimePlus $dateOrKeyword = 'current', string $format = 'long'
  ): string|TranslatableMarkup;

  /**
   * Determine if a given date is between/within a given range.
   *
   * @param string $date
   *   The date or date keyword to test.
   *
   * @param string $startDate
   *   The start date or date keyword to use as the earliest date that $date can
   *   be.
   *
   * @param string $endDate
   *   The end date or date keyword to use as the latest date that $date can be.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. This is used
   *   if $startDate is 'first' or $endDate is 'last'. Defaults to false.
   *
   * @return boolean
   *   True if $date is after or the same as $startDate and that it is before or
   *   the same as $endDate, or false if those conditions are not met.
   *
   * @see $this->getDateObject()
   *   $date, $startDate, and $endDate are passed to this to parse and create
   *   date objects for the comparison.
   */
  public function isDateBetween(
    string $date,
    string $startDate,
    string $endDate,
    bool $includeUnpublished = false
  ): bool;

  /**
   * Determine if two date ranges overlap.
   *
   * The two date ranges are considered to be overlapping if any of their days
   * occur on the same date. This means, for example, that if the end date for
   * one range and the start date for the other are on the same date, that will
   * be considered as overlapping; in that case, the start date should be moved
   * to next date to not be considered as overlapping.
   *
   * @param string $startDate1
   *   The date or date keyword to use as the start of the first date range.
   *
   * @param string $endDate1
   *   The date or date keyword to use as the end of the first date range.
   *
   * @param string $startDate2
   *   The date or date keyword to use as the start of the second date range.
   *
   * @param string $endDate2
   *   The date or date keyword to use as the end of the second date range.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content when
   *   resolving date keywords such as 'first' or 'last'. Defaults to false.
   *
   * @return bool
   *   True if there is an overlap between the two date ranges, or false if they
   *   don't overlap.
   */
  public function doDateRangesOverlap(
    string $startDate1,
    string $endDate1,
    string $startDate2,
    string $endDate2,
    bool $includeUnpublished = false
  ): bool;

}
