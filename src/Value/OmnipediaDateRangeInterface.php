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

}
