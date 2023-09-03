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

    $this->assertInstanceOf(
      DateTimePlus::class,
      $startDate,
    );

    $this->assertInstanceOf(
      DateTimePlus::class,
      $endDate,
    );

  }

  /**
   * Data provider for self::testValidDateRanges().
   *
   * @return array
   *   Valid date ranges defined as start and end date strings.
   */
  public static function validDateRangesProvider(): array {

    return [
      ['2049-09-28', '2049-10-01'],
      ['2049-09-29', '2049-10-10'],
      ['2030-01-29', '2049-10-15'],
    ];

  }

  /**
   * Test various valid date range start and end dates.
   *
   * @dataProvider validDateRangesProvider
   */
  public function testValidDateRanges(
    string $startDate, string $endDate,
  ): void {

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange = $this->createDateRange($startDate, $endDate);

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $startDateObject = $dateRange->getStartDate();

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $endDateObject = $dateRange->getEndDate();

    $this->assertEquals(false, $startDateObject->hasErrors());

    $this->assertEquals(false, $endDateObject->hasErrors());

    $this->assertEquals(true, $startDateObject < $endDateObject);

  }

  /**
   * Data provider for self::testInvalidDateRanges().
   *
   * @return array
   *   Invalid date ranges defined as start and end date strings.
   */
  public static function invalidDateRangesProvider(): array {

    return [
      ['2049-09-28', '2049-09-28'],
      ['2049-09-29', '2049-08-30'],
      ['2049-10-10', '2049-10-01'],
    ];

  }

  /**
   * Test that various invalid date range start and end dates throw exceptions.
   *
   * @dataProvider invalidDateRangesProvider
   */
  public function testInvalidDateRanges(
    string $startDate, string $endDate,
  ): void {

    $this->expectException(\LogicException::class);

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange = $this->createDateRange($startDate, $endDate);

  }

  /**
   * Data provider for self::testOverlapsWithRange().
   *
   * @return array
   */
  public static function overlapsWithRangeProvider(): array {

    return [
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

  }

  /**
   * Test that the date range overlap method correctly identifies such ranges.
   *
   * @dataProvider overlapsWithRangeProvider
   */
  public function testOverlapsWithRange(
    array $dateRange1Values, array $dateRange2Values, bool $expectOverlap,
  ): void {

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange1Object = $this->createDateRange(
      $dateRange1Values[0], $dateRange1Values[1],
    );

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange2Object = $this->createDateRange(
      $dateRange2Values[0], $dateRange2Values[1],
    );

    $this->assertEquals(
      $expectOverlap, $dateRange1Object->overlapsWithRange($dateRange2Object),
    );

  }

  /**
   * Data provider for self::testOverlapsDate().
   *
   * @return array
   */
  public static function overlapsWithDateProvider(): array {

    return [
      ['2049-09-27', false],
      ['2049-09-28', true],
      ['2049-09-30', true],
      ['2049-10-03', true],
      ['2049-10-07', false],
      ['2049-10-10', false],
    ];

  }

  /**
   * Test that the date overlap method correctly identifies such dates.
   *
   * @dataProvider overlapsWithDateProvider
   */
  public function testOverlapsDate(string $date, bool $expectOverlap): void {

    /** @var \Drupal\omnipedia_date\Value\OmnipediaDateRangeInterface */
    $dateRange = $this->createDateRange('2049-09-28', '2049-10-05');

    $this->assertEquals(
      $expectOverlap, $dateRange->overlapsDate($this->createDate($date)),
    );

  }

}
