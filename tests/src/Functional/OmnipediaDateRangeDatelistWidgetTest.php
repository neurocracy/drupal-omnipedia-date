<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Functional;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\omnipedia_core\Traits\WikiNodeProvidersTrait;

/**
 * Tests for OmnipediaDateRangeDatelistWidget.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class OmnipediaDateRangeDatelistWidgetTest extends BrowserTestBase {

  use WikiNodeProvidersTrait;

  /**
   * The default date to use in tests.
   *
   * The default date must be set for the current date service to work
   * correctly, as it needs it as a fallback. We're using a date that's not
   * expected to be used in the wiki nodes generated for this test.
   *
   * @see \Drupal\Tests\omnipedia_core\Traits\WikiNodeProvidersTrait::generateWikiDates()
   */
  protected const DEFAULT_DATE = '1989-12-16';

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The machine name of the entity used for the tests.
   */
  protected const TEST_ENTITY_TYPE = 'test_entity_with_date_range';

  /**
   * The Omnipedia current date service.
   *
   * @var \Drupal\omnipedia_date\Service\CurrentDateInterface
   */
  protected readonly CurrentDateInterface $currentDate;

  /**
   * The Omnipedia default date service.
   *
   * @var \Drupal\omnipedia_date\Service\DefaultDateInterface
   */
  protected readonly DefaultDateInterface $defaultDate;

  /**
   * The Drupal entity type plug-in manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected readonly EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'omnipedia_date', 'omnipedia_date_entity_date_range_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $definedDates = static::generateWikiDates();

    // Prepend the default date so that the date deriver includes it when
    // generating derivatives.
    //
    // @see \Drupal\omnipedia_date\Plugin\Deriver\OmnipediaDateDeriver
    \array_unshift($definedDates, self::DEFAULT_DATE);

    // Set the defined dates to state so that the Omnipedia defined dates
    // service finds those and doesn't attempt to build them from the wiki node
    // tracker, which would return no values as we haven't created any wiki
    // nodes yet.
    $this->container->get('state')->set(self::DEFINED_DATES_STATE_KEY, [
      'all'       => $definedDates,
      'published' => $definedDates,
    ]);

    $this->currentDate = $this->container->get('omnipedia_date.current_date');

    $this->defaultDate = $this->container->get('omnipedia_date.default_date');

    $this->defaultDate->set(self::DEFAULT_DATE);

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $user = $this->drupalCreateUser([
      'access administration pages',
      'edit test_entity_with_date_range entity',
    ]);

    $this->drupalLogin($user);

  }

  /**
   * Data provider for entities with date ranges.
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
        'expected' => ['value' => '2049-09-28', 'end_value' => '2049-10-01'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => '2049-09-30',
          'end_value' => '2049-10-10',
        ]],
        'expected' => ['value' => '2049-09-30', 'end_value' => '2049-10-10'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => '2049-09-30',
          'end_value' => null,
        ]],
        'expected' => ['value' => '2049-09-30', 'end_value' => 'last'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => null,
          'end_value' => '2049-10-10',
        ]],
        'expected' => ['value' => 'first', 'end_value' => '2049-10-10'],
      ],
      [
        'values' => ['date_range' => [
          'value'     => null,
          'end_value' => null,
        ]],
        'expected' => ['value' => 'first', 'end_value' => 'last'],
      ],
    ];

  }

  /**
   * Test that saved date range on the test entity is pre-filled on edit form.
   *
   * @dataProvider entityDateRangeProvider
   */
  public function testEditFormLoad(array $values, array $expected): void {

    /** @var \Drupal\Core\Entity\EntityStorageInterface The entity storage for this entity type. */
    $storage = $this->entityTypeManager->getStorage(self::TEST_ENTITY_TYPE);

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
    $entity = $storage->create($values);

    $storage->save($entity);

    $this->drupalGet($entity->toUrl('edit-form'));

    foreach ($values['date_range'] as $valueKey => $value) {

      /** @var string The name of our <select> element. */
      $selectName = 'date_range[0][' . $valueKey . ']';

      $this->assertSession()->selectExists($selectName);

      $this->assertSession()->fieldValueEquals(
        $selectName, $expected[$valueKey],
      );

    }

  }

  /**
   * Test that submitting the test entity edit form updates date range values.
   *
   * @dataProvider entityDateRangeProvider
   */
  public function testEditFormSubmit(array $values, array $expected): void {

    /** @var \Drupal\Core\Entity\EntityStorageInterface The entity storage for this entity type. */
    $storage = $this->entityTypeManager->getStorage(self::TEST_ENTITY_TYPE);

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
    $entity = $storage->create(['date_range' => [
      'value'     => null,
      'end_value' => null,
    ]]);

    $storage->save($entity);

    $this->drupalGet($entity->toUrl('edit-form'));

    $submitValues = [];

    foreach ($values['date_range'] as $valueKey => $value) {

      /** @var string The name of our <select> element. */
      $selectName = 'date_range[0][' . $valueKey . ']';

      $submitValues[$selectName] = $expected[$valueKey];

    }

    $this->submitForm($submitValues, 'Save');

    foreach ($values['date_range'] as $valueKey => $value) {

      /** @var string The name of our <select> element. */
      $selectName = 'date_range[0][' . $valueKey . ']';

      $this->assertSession()->selectExists($selectName);

      $this->assertSession()->fieldValueEquals(
        $selectName, $expected[$valueKey],
      );

    }

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
    $reloadedEntity = $storage->load($entity->id());

    $this->assertEquals($expected['value'], $reloadedEntity->getStartDate());

    $this->assertEquals($expected['end_value'], $reloadedEntity->getEndDate());

  }

}
