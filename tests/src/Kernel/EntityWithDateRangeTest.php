<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Kernel;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
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

    $this->assertIsObject($entity);

    $this->assertEquals($expected['start'], $entity->getStartDate());

    $this->assertEquals($expected['end'], $entity->getEndDate());

  }

}
