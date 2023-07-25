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

    if ($this->startDate === $this->endDate) {
      throw new \LogicException(
        'The start and end dates cannot be the same!'
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

}
