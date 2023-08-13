<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Kernel;

use Drupal\Core\State\StateInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;

/**
 * Tests for the Omnipedia defined dates service.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class DefinedDatesTest extends KernelTestBase {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The Drupal state system manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected readonly StateInterface $stateManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['omnipedia_core', 'omnipedia_date'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->stateManager = $this->container->get('state');

  }

  /**
   * Data provider for self::testFindDefinedDates().
   *
   * @return array
   */
  public static function findDefinedDatesProvider(): array {

    return [
      [
        'data' => [
          'dates' => [
            '2049-09-28' => ['1', '2'],
            '2049-09-29' => ['3', '4'],
          ],
          'nodes' => [
            1  => ['date' => '2049-09-28', 'Page 1', 'published' => false],
            2  => ['date' => '2049-09-28', 'Page 2', 'published' => false],

            3  => ['date' => '2049-09-29', 'Page 1', 'published' => true],
            4  => ['date' => '2049-09-29', 'Page 2', 'published' => true],
          ],
        ],
        'expected' => [
          'all' => [
            '2049-09-28',
            '2049-09-29',
          ],
          'published' => [
            '2049-09-29',
          ],
        ],
      ],
      [
        'data' => [
          'dates' => [
            '2049-09-28' => ['1',  '2'],
            '2049-09-29' => ['3',  '4'],
            '2049-09-30' => ['5',  '6',  '7'],
            '2049-10-01' => ['8',  '9',  '10'],
            '2049-10-02' => ['11', '12', '13', '14'],
          ],
          'nodes' => [
            1  => ['date' => '2049-09-28', 'Page 1', 'published' => false],
            2  => ['date' => '2049-09-28', 'Page 2', 'published' => false],

            3  => ['date' => '2049-09-29', 'Page 1', 'published' => true],
            4  => ['date' => '2049-09-29', 'Page 2', 'published' => true],

            5  => ['date' => '2049-09-30', 'Page 1', 'published' => true],
            6  => ['date' => '2049-09-30', 'Page 2', 'published' => true],
            7  => ['date' => '2049-09-30', 'Page 3', 'published' => true],

            8  => ['date' => '2049-10-01', 'Page 1', 'published' => true],
            9  => ['date' => '2049-10-01', 'Page 2', 'published' => true],
            10 => ['date' => '2049-10-01', 'Page 3', 'published' => true],

            11 => ['date' => '2049-10-02', 'Page 1', 'published' => true],
            12 => ['date' => '2049-10-02', 'Page 2', 'published' => true],
            13 => ['date' => '2049-10-02', 'Page 3', 'published' => true],
            14 => ['date' => '2049-10-02', 'Page 4', 'published' => true],
          ],
        ],
        'expected' => [
          'all' => [
            '2049-09-28',
            '2049-09-29',
            '2049-09-30',
            '2049-10-01',
            '2049-10-02',
          ],
          'published' => [
            '2049-09-29',
            '2049-09-30',
            '2049-10-01',
            '2049-10-02',
          ],
        ],
      ],
    ];

  }

  /**
   * Test building list of dates returned by a mocked wiki node tracker.
   *
   * @dataProvider findDefinedDatesProvider
   */
  public function testFindDefinedDates(array $data, array $expected): void {

    /** @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface */
    $wikiNodeTracker = $this->prophesize(WikiNodeTrackerInterface::class);

    $wikiNodeTracker->getTrackedWikiNodeData()->willReturn($data);

    $this->container->set(
      'omnipedia.wiki_node_tracker', $wikiNodeTracker->reveal(),
    );

    /** @var \Drupal\omnipedia_date\Service\DefinedDatesInterface */
    $definedDates = $this->container->get('omnipedia_date.defined_dates');

    $definedDates->find();

    /** @var array|null */
    $stateData = $this->stateManager->get(self::DEFINED_DATES_STATE_KEY);

    $this->assertEquals($expected, $stateData);

  }

  /**
   * Data provider for self::testGetDefinedDates().
   *
   * @return array
   */
  public static function getDefinedDatesProvider(): array {

    return [
      [
        'dates' => [
          'all' => [
            '2049-09-28',
            '2049-09-29',
          ],
          'published' => [
            '2049-09-29',
          ],
        ],
      ],
      [
        'dates' => [
          'all' => [
            '2049-09-28',
            '2049-09-29',
            '2049-09-30',
            '2049-10-01',
            '2049-10-02',
          ],
          'published' => [
            '2049-09-29',
            '2049-09-30',
            '2049-10-01',
            '2049-10-02',
          ],
        ],
      ],
      [
        'dates' => [
          'all' => [
            '2049-09-28',
            '2049-09-29',
            '2049-09-30',
            '2049-10-01',
            '2049-10-02',
          ],
          'published' => [
          ],
        ],
      ],
    ];

  }

  /**
   * Test getting defined dates.
   *
   * @dataProvider getDefinedDatesProvider
   */
  public function testGetDefinedDates(array $dates): void {

    /** @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface */
    $wikiNodeTracker = $this->prophesize(WikiNodeTrackerInterface::class);

    $this->container->set(
      'omnipedia.wiki_node_tracker', $wikiNodeTracker->reveal(),
    );

    /** @var \Drupal\omnipedia_date\Service\DefinedDatesInterface */
    $definedDates = $this->container->get('omnipedia_date.defined_dates');

    // Test that it picks up the dates saved to state.
    $this->stateManager->set(self::DEFINED_DATES_STATE_KEY, $dates);

    $this->assertEquals($dates['published'], $definedDates->get(false));

    $this->assertEquals($dates['all'], $definedDates->get(true));

    // Then delete the state entry to test that it's saved the dates to its own
    // property and that it returns that as its first choice. If neither the
    // state entry nor its property have defined dates saved, it would try to
    // retrieve the dates from the mocked wiki node tracker service which in
    // turn would fail the subsequent assertions.
    $this->stateManager->delete(self::DEFINED_DATES_STATE_KEY);

    $this->assertEquals($dates['published'], $definedDates->get(false));

    $this->assertEquals($dates['all'], $definedDates->get(true));

  }

  /**
   * Test getting the first defined date.
   *
   * @dataProvider getDefinedDatesProvider
   */
  public function testGetFirstDate(array $dates): void {

    /** @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface */
    $wikiNodeTracker = $this->prophesize(WikiNodeTrackerInterface::class);

    $this->container->set(
      'omnipedia.wiki_node_tracker', $wikiNodeTracker->reveal(),
    );

    /** @var \Drupal\omnipedia_date\Service\DefinedDatesInterface */
    $definedDates = $this->container->get('omnipedia_date.defined_dates');

    $this->stateManager->set(self::DEFINED_DATES_STATE_KEY, $dates);

    foreach ([
      'all'       => true,
      'published' => false,
    ] as $key => $includeUnpublished) {

      if (count($dates[$key]) === 0) {

        $this->expectException(\UnexpectedValueException::class);

        $definedDates->getFirstDate($includeUnpublished);

      } else {

        $this->assertEquals(
          \reset($dates[$key]),
          $definedDates->getFirstDate($includeUnpublished),
        );

      }

    }

  }

  /**
   * Test getting the last defined date.
   *
   * @dataProvider getDefinedDatesProvider
   */
  public function testGetLastDate(array $dates): void {

    /** @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface */
    $wikiNodeTracker = $this->prophesize(WikiNodeTrackerInterface::class);

    $this->container->set(
      'omnipedia.wiki_node_tracker', $wikiNodeTracker->reveal(),
    );

    /** @var \Drupal\omnipedia_date\Service\DefinedDatesInterface */
    $definedDates = $this->container->get('omnipedia_date.defined_dates');

    $this->stateManager->set(self::DEFINED_DATES_STATE_KEY, $dates);

    foreach ([
      'all'       => true,
      'published' => false,
    ] as $key => $includeUnpublished) {

      if (count($dates[$key]) === 0) {

        $this->expectException(\UnexpectedValueException::class);

        $definedDates->getLastDate($includeUnpublished);

      } else {

        $this->assertEquals(
          \end($dates[$key]),
          $definedDates->getLastDate($includeUnpublished),
        );

      }

    }

  }

}
