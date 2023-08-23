<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Kernel;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;

/**
 * Tests for entities with date ranges.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 *
 * @see \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface
 *
 * @see \Drupal\omnipedia_date\Entity\EntityWithDateRangeTrait
 */
class EntityWithDateRangeTest extends KernelTestBase {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The machine name of the entity used for the tests.
   */
  protected const TEST_ENTITY_TYPE = 'test_entity_with_date_range';

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected readonly EntityTypeManagerInterface $entityTypeManager;

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
    '2049-10-03',
    '2049-10-04',
    '2049-10-05',
    '2049-10-06',
    '2049-10-07',
    '2049-10-08',
    '2049-10-09',
    '2049-10-10',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'omnipedia_core', 'omnipedia_date', 'omnipedia_date_entity_date_range_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->installEntitySchema(self::TEST_ENTITY_TYPE);

    // Set the defined dates to state so that the Omnipedia defined dates
    // service finds those and doesn't attempt to build them from the wiki node
    // tracker, which would return no values as we haven't created any wiki
    // nodes for this test.
    //
    // These are needed for the constraint validation test as the validator will
    // call the Timeline service which in turn leads to various calls other
    // services, including fetching the available defined dates.
    $this->container->get('state')->set(self::DEFINED_DATES_STATE_KEY, [
      'all'       => static::$definedDatesData,
      'published' => static::$definedDatesData,
    ]);

  }

  /**
   * Reloads the given entity from the storage and returns it.
   *
   * @param \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface $entity
   *   The entity to be reloaded.
   *
   * @return \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface
   *   The reloaded entity.
   *
   * @see \Drupal\KernelTests\Core\Entity\EntityKernelTestBase::reloadEntity()
   *   Adapted from this core class.
   */
  protected function reloadEntity(
    EntityWithDateRangeInterface $entity,
  ): EntityWithDateRangeInterface {

    /** @var \Drupal\Core\Entity\EntityStorageInterface The entity storage for this entity type. */
    $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());

    $storage->resetCache([$entity->id()]);

    return $storage->load($entity->id());
  }

  /**
   * Data provider for self::testEntityDateRange().
   *
   * @return array
   */
  public static function entityDateRangeProvider(): array {

    return [
      [
        'values' => ['date_range' => [
          'value'     => '2049-09-28',
          'end_value' => '2049-10-01',
        ]],
        'expected' => ['start' => '2049-09-28', 'end' => '2049-10-01'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => '2049-09-30',
          'end_value' => '2049-10-10',
        ]],
        'expected' => ['start' => '2049-09-30', 'end' => '2049-10-10'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => '2049-09-30',
          'end_value' => null,
        ]],
        'expected' => ['start' => '2049-09-30', 'end' => 'last'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => null,
          'end_value' => '2049-10-10',
        ]],
        'expected' => ['start' => 'first', 'end' => '2049-10-10'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => null,
          'end_value' => null,
        ]],
        'expected' => ['start' => 'first', 'end' => 'last'],
      ],
    ];

  }

  /**
   * Test setting and getting date range values on the test entity type.
   *
   * @dataProvider entityDateRangeProvider
   */
  public function testEntityDateRange(array $values, array $expected): void {

    /** @var \Drupal\Core\Entity\EntityStorageInterface The entity storage for this entity type. */
    $storage = $this->entityTypeManager->getStorage(self::TEST_ENTITY_TYPE);

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
    $entity = $storage->create($values);

    $storage->save($entity);

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
    $entity = $this->reloadEntity($entity);

    $this->assertEquals($expected['start'], $entity->getStartDate());

    $this->assertEquals($expected['end'], $entity->getEndDate());

  }

  /**
   * Data provider for self::testNonOverlappingEntityDateRange().
   *
   * @return array
   */
  public static function nonOverlappingEntityDateRangeProvider(): array {

    return [
      [
        'values' => [
          ['date_range' => [
            'value'     => '2049-09-28',
            'end_value' => '2049-10-01',
          ]],
          ['date_range' => [
            'value'     => '2049-10-02',
            'end_value' => '2049-10-03',
          ]],
          ['date_range' => [
            'value'     => '2049-10-04',
            'end_value' => '2049-10-08',
          ]],
        ],
        'expected' => [
          ['violationsCount' => 0],
          ['violationsCount' => 0],
          ['violationsCount' => 0],
        ],
      ],
      [
        'values' => [
          ['date_range' => [
            'value'     => '2049-09-28',
            'end_value' => '2049-10-01',
          ]],
          ['date_range' => [
            'value'     => '2049-10-01',
            'end_value' => '2049-10-03',
          ]],
          ['date_range' => [
            'value'     => '2049-10-02',
            'end_value' => '2049-10-08',
          ]],
          ['date_range' => [
            'value'     => '2049-09-28',
            'end_value' => '2049-10-10',
          ]],
        ],
        'expected' => [
          ['violationsCount' => 0],
          ['violationsCount' => 1],
          ['violationsCount' => 1],
          ['violationsCount' => 3],
        ],
      ],
    ];

  }

  /**
   * Test the non-overlapping date range constraint validation.
   *
   * @dataProvider nonOverlappingEntityDateRangeProvider
   *
   * @see \Drupal\KernelTests\Core\Entity\EntityFieldTest::testEntityConstraintValidation()
   *   Core class testing entity field constraint validation; used for
   *   reference.
   */
  public function testNonOverlappingEntityDateRange(
    array $providerValues, array $expected,
  ): void {

    /** @var \Drupal\Core\Entity\EntityStorageInterface The entity storage for this entity type. */
    $storage = $this->entityTypeManager->getStorage(self::TEST_ENTITY_TYPE);

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface[] */
    $entities = [];

    foreach ($providerValues as $key => $values) {

      /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
      $entities[$key] = $storage->create($values);

      // The entities must be saved to storage so that the constraint validator
      // finds them when
      $storage->save($entities[$key]);

      $violations = $entities[$key]->date_range->validate();

      $this->assertEquals(
        $expected[$key]['violationsCount'],
        $violations->count(),
      );

    }

  }

}
