<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Functional;

use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Tests for the Omnipedia current date service and persisting the date.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class CurrentDatePersistTest extends BrowserTestBase {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

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
   * The user entity created for this test.
   *
   * @var \Drupal\user\UserInterface
   */
  protected readonly UserInterface $testUser;

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
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['omnipedia_date'];

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

    $this->currentDate = $this->container->get('omnipedia_date.current_date');

    $this->defaultDate = $this->container->get('omnipedia_date.default_date');

    $this->testUser = $this->drupalCreateUser([
      'access content',
    ]);

    $this->drupalCreateContentType(['type' => 'page']);

    /** @var \Drupal\node\NodeInterface A node to set as the front page. */
    $node = $this->drupalCreateNode([
      'type'  => 'page',
    ]);

    /** @var \Drupal\Core\Config\Config */
    $config = $this->container->get('config.factory')->getEditable(
      'system.site',
    );

    $config->set('page.front', $node->toUrl()->toString())->save();

  }

  /**
   * Defined dates data provider.
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
   * Test that setting the current date persists between requests.
   *
   * @dataProvider defaultDatesProvider
   */
  public function testPersistentCurrentDate(string $date): void {

    $this->defaultDate->set($date);

    $this->drupalLogin($this->testUser);

    // Loop through all the defined dates except for the current default date.
    foreach (\array_diff(static::$definedDatesData, [$date]) as $otherDate) {

      $this->currentDate->set($otherDate);

      $this->assertEquals($otherDate, $this->currentDate->get());

      // Navigate to the base URL, i.e. the front page.
      $this->drupalGet('');

      $this->assertEquals($otherDate, $this->currentDate->get());

    }

  }

}
