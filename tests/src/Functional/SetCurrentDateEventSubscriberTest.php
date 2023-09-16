<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Functional;

use Drupal\omnipedia_core\Entity\WikiNodeInfo;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\omnipedia_date_current_date_test\EventSubscriber\Kernel\CurrentDateHeaderEventSubscriber;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\omnipedia_core\Traits\WikiNodeProvidersTrait;
use Drupal\typed_entity\EntityWrapperInterface;

/**
 * Tests for EventSubscriber\Kernel\SetCurrentDateEventSubscriber.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 *
 * @see \Drupal\omnipedia_date_current_date_test\EventSubscriber\Kernel\CurrentDateHeaderEventSubscriber
 *   Event subscriber that outputs the current date as an HTTP header to work
 *   around the fact that we can't easily access the session data of the
 *   requested pages in a functional test.
 *
 * @see \Drupal\Tests\system\Functional\Session\SessionTest
 *   Uses the same approach to getting session data via HTTP headers output by
 *   an event subscriber in a test module.
 */
class SetCurrentDateEventSubscriberTest extends BrowserTestBase {

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
   * The Typed Entity repository manager.
   *
   * @var \Drupal\typed_entity\EntityWrapperInterface
   */
  protected readonly EntityWrapperInterface $typedEntityRepositoryManager;

  /**
   * The Omnipedia wiki node tracker service.
   *
   * @var \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface
   */
  protected readonly WikiNodeTrackerInterface $wikiNodeTracker;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'omnipedia_date', 'omnipedia_date_current_date_test',
  ];

  /**
   * Node objects for the tests, keyed by their nid.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected array $nodes;

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

    $this->typedEntityRepositoryManager = $this->container->get(
      'Drupal\typed_entity\RepositoryManager',
    );

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

    // Set the default date, but use a date
    $this->defaultDate->set(self::DEFAULT_DATE);

    $this->drupalCreateContentType(['type' => 'page']);

    /** @var \Drupal\node\NodeInterface A node to set as the front page. */
    $node = $this->drupalCreateNode([
      'type'  => 'page',
    ]);

    $parameters = static::generateWikiNodeValues();

    /** @var \Drupal\node\NodeInterface[] Node objects keyed by their nid. */
    $nodes = [];

    foreach ($parameters as $values) {

      /** @var \Drupal\node\NodeInterface */
      $node = $this->drupalCreateNode($values);

      $this->wikiNodeTracker->trackWikiNode($node);

      $nodes[$node->id()] = $node;

      // Roughly 1 out of 3 times, insert a non-wiki 'page' node type.
      if (\rand(1, 3) === 1) {

        /** @var \Drupal\node\NodeInterface */
        $node = $this->drupalCreateNode(['type' => 'page']);

        $nodes[$node->id()] = $node;

      }

      $this->nodes = $nodes;

    }

  }

  /**
   * Data provider for canonical route test, defining different user types.
   *
   * @return array
   */
  public static function canonicalRouteUserTypeProvider(): array {

    return [
      [false], // === anonymous, i.e. don't create a user account.
      [[['access content']]],
      [[[
        'access content',
        'edit any ' . WikiNodeInfo::TYPE . ' content',
      ]]],
    ];

  }

  /**
   * Test that visiting various wiki node canonical routes updates current date.
   *
   * @dataProvider canonicalRouteUserTypeProvider
   *
   * @todo Also test that the current date remains that of the last wiki node
   *   visited when visiting a non-wiki node.
   */
  public function testCanonicalRoute(bool|array $userParameters): void {

    if (\is_array($userParameters)) {

      $user = \call_user_func_array(
        [$this, 'drupalCreateUser'], $userParameters,
      );

      $this->drupalLogin($user);

    }

    foreach ($this->nodes as $nid => $node) {

      /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface */
      $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

      $this->drupalGet($node->toUrl());

      /** @var int The actual status code from the Drupal response. */
      $actualStatusCode = $this->getSession()->getStatusCode();

      // Attempts to catch any unexpected errors that may result in the header
      // being correctly output but still failing on some level thereafter.
      $this->assertLessThan(400, $actualStatusCode, \sprintf(
        'Expected a status code of less than 400! Got: %d', $actualStatusCode,
      ));

      if ($wrappedNode->isWikiNode() === true) {

        $this->assertSession()->responseHeaderEquals(
          CurrentDateHeaderEventSubscriber::HEADER, $wrappedNode->getWikiDate(),
        );

      }

    }

  }

  /**
   * Test that visiting the edit and preview routes update the current date.
   */
  public function testEditAndPreviewRoutes(): void {

    $user = $this->drupalCreateUser([
      'access content',
      'edit any ' . WikiNodeInfo::TYPE . ' content',
      'edit any page content',
    ]);

    $this->drupalLogin($user);

    foreach ($this->nodes as $nid => $node) {

      /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface */
      $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

      $this->drupalGet($node->toUrl('edit-form'));

      /** @var int The actual status code from the Drupal response. */
      $actualStatusCode = $this->getSession()->getStatusCode();

      // Attempts to catch any unexpected errors that may result in the header
      // being correctly output but still failing on some level thereafter.
      $this->assertLessThan(400, $actualStatusCode, \sprintf(
        'Expected a status code of less than 400! Got: %d', $actualStatusCode,
      ));

      if ($wrappedNode->isWikiNode() === true) {

        $this->assertSession()->responseHeaderEquals(
          CurrentDateHeaderEventSubscriber::HEADER, $wrappedNode->getWikiDate(),
        );

      }

      $this->submitForm([], 'Preview');

      /** @var int The actual status code from the Drupal response. */
      $actualStatusCode = $this->getSession()->getStatusCode();

      // Attempts to catch any unexpected errors that may result in the header
      // being correctly output but still failing on some level thereafter.
      $this->assertLessThan(400, $actualStatusCode, \sprintf(
        'Expected a status code of less than 400! Got: %d', $actualStatusCode,
      ));

      if ($wrappedNode->isWikiNode() === true) {

        $this->assertSession()->responseHeaderEquals(
          CurrentDateHeaderEventSubscriber::HEADER, $wrappedNode->getWikiDate(),
        );

      }

    }

  }

}
