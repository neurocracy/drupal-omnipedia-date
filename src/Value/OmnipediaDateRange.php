<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Value;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface;

/**
 * Omnipedia date range value object.
 */
class OmnipediaDateRange implements OmnipediaDateRangeInterface {

  /**
   * Constructor; validates and saves dependencies.
   *
   * @param \Drupal\Component\Datetime\DateTimePlus $startDate
   *   The start date for this range.
   *
   * @param \Drupal\Component\Datetime\DateTimePlus $endDate
   *   The end date for this range.
   *
   * @throws \LogicException
   *   If the start date is after the end date, or if the start and end dates
   *   are the same.
   */
  public function __construct(
    protected readonly DateTimePlus $startDate,
    protected readonly DateTimePlus $endDate,
  ) {

    if ($this->startDate > $this->endDate) {
      throw new \LogicException(
        'The start date cannot be after the end date!'
      );
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate(): DateTimePlus {
    return $this->startDate;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate(): DateTimePlus {
    return $this->endDate;
  }

  /**
   * {@inheritdoc}
   */
  public function overlapsDate(DateTimePlus $date): bool {

    return $this->getStartDate() <= $date && $date <= $this->getEndDate();

  }

  /**
   * {@inheritdoc}
   */
  public function overlapsWithRange(
    OmnipediaDateRangeInterface $dateRange
  ): bool {

    // Does our date ranges's start date fall between the other date range's
    // start and end dates?
    //
    //   |----| <-- Us
    // |----|   <-- Them
    if (
      $this->getStartDate() >= $dateRange->getStartDate() &&
      $this->getStartDate() <= $dateRange->getEndDate()
    ) {
      return true;
    }

    // Does the our date range's end date fall between the other date range's
    // start and end dates?
    //
    // |----|   <-- Us
    //   |----| <-- Them
    if (
      $this->getEndDate() >= $dateRange->getStartDate() &&
      $this->getEndDate() <= $dateRange->getEndDate()
    ) {
      return true;
    }

    // Does the our date range span across the entirety of the other date
    // range? Note that the reverse should be caught by one of the preceding
    // checks, so we don't need to check here.
    //
    // |-------|  <-- Us
    //   |---|    <-- Them
    if (
      $this->getStartDate() <= $dateRange->getStartDate() &&
      $this->getEndDate()   >= $dateRange->getEndDate()
    ) {
      return true;
    }

    return false;

  }

}
