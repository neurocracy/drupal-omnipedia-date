<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Functional;

use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
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
 */
class SetCurrentDateEventSubscriberTest extends BrowserTestBase {

  use WikiNodeProvidersTrait;

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  // /**
  //  * The Omnipedia current date service.
  //  *
  //  * @var \Drupal\omnipedia_date\Service\CurrentDateInterface
  //  */
  // protected readonly CurrentDateInterface $currentDate;

  // /**
  //  * The Omnipedia default date service.
  //  *
  //  * @var \Drupal\omnipedia_date\Service\DefaultDateInterface
  //  */
  // protected readonly DefaultDateInterface $defaultDate;

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
  protected static $modules = ['omnipedia_date'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    // // Set the defined dates to state so that the Omnipedia defined dates
    // // service finds those and doesn't attempt to build them from the wiki node
    // // tracker, which would return no values as we haven't created any wiki
    // // nodes for this test.
    // $this->container->get('state')->set(self::DEFINED_DATES_STATE_KEY, [
    //   'all'       => static::$definedDatesData,
    //   'published' => static::$definedDatesData,
    // ]);

    // $this->currentDate = $this->container->get('omnipedia_date.current_date');

    // $this->defaultDate = $this->container->get('omnipedia_date.default_date');

    $this->typedEntityRepositoryManager = $this->container->get(
      'Drupal\typed_entity\RepositoryManager',
    );

    $this->wikiNodeTracker = $this->container->get(
      'omnipedia.wiki_node_tracker'
    );

    // $this->testUser = $this->drupalCreateUser([
    //   'access content',
    // ]);

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

  public function testNavigateAnonymous(): void {}

}
