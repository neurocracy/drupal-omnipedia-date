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
  public function findDefinedDatesProvider(): array {

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

}
