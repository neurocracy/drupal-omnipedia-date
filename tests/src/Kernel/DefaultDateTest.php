<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_date\Service\DefaultDateInterface;

/**
 * Tests for the Omnipedia default date service.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class DefaultDateTest extends KernelTestBase {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The Omnipedia default date service.
   *
   * @var \Drupal\omnipedia_date\Service\DefaultDateInterface
   */
  protected readonly DefaultDateInterface $defaultDate;

  /**
   * Defined dates to generate for the test, in storage format.
   *
   * @var string[]
   */
  protected static array $definedDatesData = [
    '2049-09-28',
    '2049-09-29',
    '2049-09-30',
    '2049-10-01',
    '2049-10-02',
    '2049-10-05',
    '2049-10-10',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['omnipedia_core', 'omnipedia_date'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    // Set the defined dates to state so that the Omnipedia defined dates
    // service finds those and doesn't attempt to build them from the wiki node
    // tracker, which would return no values as we haven't created any wiki
    // nodes for this test.
    $this->container->get('state')->set(self::DEFINED_DATES_STATE_KEY, [
      'all'       => static::$definedDatesData,
      'published' => static::$definedDatesData,
    ]);

    $this->defaultDate = $this->container->get('omnipedia_date.default_date');

  }

  /**
   * Data provider for self::testDefaultDates().
   *
   * @return array
   */
  public static function defaultDatesProvider(): array {

    $data = [];

    foreach (static::$definedDatesData as $date) {
      $data[] = [$date];
    }

    return $data;

  }

  /**
   * Test setting and getting the default date.
   *
   * @dataProvider defaultDatesProvider
   */
  public function testSetGetDefaultDates(string $date): void {

    $this->defaultDate->set($date);

    $this->assertEquals($date, $this->defaultDate->get());

  }

  /**
   * Test that attempting to get default date when none set throws an exception.
   */
  public function testNoDefaultDate(): void {

    $this->expectException(\UnexpectedValueException::class);

    $this->defaultDate->get();

  }

}
