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
 *
 * @see \Drupal\Tests\Component\Datetime\DateTimePlusTest
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
      $this->createDate($start),
      $this->createDate($end),
    );

  }

  /**
   * Create a DateTimePlus instance with the provided date.
   *
   * @param string $date
   *   A date in storage format.
   *
   * @return \Drupal\Component\Datetime\DateTimePlus
   */
  protected function createDate(string $date): DateTimePlus {
    return DateTimePlus::createFromFormat(self::DATE_FORMAT, $date, null);
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

  /**
   * Test that the date range overlap method correctly identifies such ranges.
   */
  public function testOverlapsWithRange(): void {

    $ranges = [
      //   |----|
      // |----|
      [['2049-10-01', '2049-10-10'], ['2049-09-28', '2049-10-05'], true],
      // |----|
      //   |----|
      [['2049-09-28', '2049-10-05'], ['2049-10-01', '2049-10-10'], true],
      // |-------|
      //   |---|
      [['2049-09-28', '2049-10-10'], ['2049-10-01', '2049-10-05'], true],
      //   |---|
      // |-------|
      [['2049-10-01', '2049-10-05'], ['2049-09-28', '2049-10-10'], true],
      // |---|
      // |-------|
      [['2049-09-28', '2049-10-05'], ['2049-09-28', '2049-10-10'], true],
      //     |---|
      // |-------|
      [['2049-09-28', '2049-10-10'], ['2049-10-05', '2049-10-10'], true],
      // |---|
      //        |---|
      [['2049-09-28', '2049-10-01'], ['2049-10-05', '2049-10-10'], false],
      //        |---|
      // |---|
      [['2049-10-05', '2049-10-10'], ['2049-09-28', '2049-10-01'], false],
    ];

    foreach ($ranges as $values) {

      /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
      $dateRange1 = $this->createDateRange($values[0][0], $values[0][1]);

      /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
      $dateRange2 = $this->createDateRange($values[1][0], $values[1][1]);

      $this->assertEquals(
        $values[2], $dateRange1->overlapsWithRange($dateRange2)
      );

    }

  }

  /**
   * Test that the date overlap method correctly identifies such dates.
   */
  public function testOverlapsDate(): void {

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange = $this->createDateRange('2049-09-28', '2049-10-05');

    foreach ([
      '2049-09-27' => false,
      '2049-09-28' => true,
      '2049-09-30' => true,
      '2049-10-03' => true,
      '2049-10-07' => false,
      '2049-10-10' => false,
    ] as $date => $expected) {
      $this->assertEquals(
        $expected, $dateRange->overlapsDate($this->createDate($date))
      );
    }

  }

}
