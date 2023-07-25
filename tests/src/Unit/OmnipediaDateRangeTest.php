<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Unit;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;
use Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface;
use Drupal\omnipedia_date\Value\OmnipediaDateRange;
use Drupal\Tests\UnitTestCase;

/**
 * Provides unit tests for the OmnipediaDateRange value object.
 *
 * @coversDefaultClass \Drupal\omnipedia_date\Value\OmnipediaDateRange
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class OmnipediaDateRangeTest extends UnitTestCase {

  /**
   * The the storage date format.
   */
  protected const DATE_FORMAT = OmnipediaDateInterface::DATE_FORMAT_STORAGE;

  /**
   * Create an OmnipediaDateRange object from a start and end date.
   *
   * @param string $start
   *   The start date in the storage format.
   *
   * @param string $end
   *   The end date in the storage format.
   *
   * @return \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface
   */
  protected function createDateRange(
    string $start, string $end
  ): OmnipediaDateRangeInterface {

    return new OmnipediaDateRange(
      DateTimePlus::createFromFormat(self::DATE_FORMAT, $start, null),
      DateTimePlus::createFromFormat(self::DATE_FORMAT, $end, null),
    );

  }

  /**
   * Test that the date range object returns the expected date object type.
   */
  public function testDateObjectType(): void {

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange = $this->createDateRange('2049-09-28', '2049-10-01');

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $startDate = $dateRange->getStartDate();

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $endDate = $dateRange->getEndDate();

    $this->assertEquals(true, $startDate instanceof DateTimePlus);

    $this->assertEquals(true, $endDate instanceof DateTimePlus);

  }

  /**
   * Test various valid date range start and end dates.
   */
  public function testValidDateRanges(): void {

    /** @var array[] Valid date ranges defined as start and end date strings. */
    $ranges = [
      ['2049-09-28', '2049-10-01'],
      ['2049-09-29', '2049-10-10'],
      ['2030-01-29', '2049-10-15'],
    ];

    foreach ($ranges as $range) {

      /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
      $dateRange = $this->createDateRange($range[0], $range[1]);

      /** @var \Drupal\Component\Datetime\DateTimePlus */
      $startDate = $dateRange->getStartDate();

      /** @var \Drupal\Component\Datetime\DateTimePlus */
      $endDate = $dateRange->getEndDate();

      $this->assertEquals(false, $startDate->hasErrors());

      $this->assertEquals(false, $endDate->hasErrors());

      $this->assertEquals(true, $startDate < $endDate);

    }

  }

  /**
   * Test that various invalid date range start and end dates throw exceptions.
   */
  public function testInvalidDateRanges(): void {

    /** @var array[] Invalid date ranges defined as start and end date strings. */
    $ranges = [
      ['2049-09-28', '2049-09-28'],
      ['2049-09-29', '2049-08-30'],
      ['2049-10-10', '2049-10-01'],
    ];

    foreach ($ranges as $range) {

      $this->expectException(\LogicException::class);

      /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
      $dateRange = $this->createDateRange($range[0], $range[1]);

    }

  }

}
