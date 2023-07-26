<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Kernel;

use Drupal\Core\State\StateInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_date\PluginCollection\OmnipediaDateLazyPluginCollection;
use Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface;

/**
 * Tests for the Omnipedia date lazy plug-in collection.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 *
 * @see \Drupal\Tests\Core\Plugin\DefaultLazyPluginCollectionTest
 *
 * @see \Drupal\Tests\Core\Plugin\LazyPluginCollectionTestBase
 */
class OmnipediaDateLazyPluginCollectionTest extends KernelTestBase {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The Omnipedia Date plug-in manager.
   *
   * @var \Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface
   */
  protected readonly OmnipediaDateManagerInterface $datePluginManager;

  /**
   * The Omnipedia Date lazy plug-in collection.
   *
   * @var \Drupal\omnipedia_date\PluginCollection\OmnipediaDateLazyPluginCollection
   */
  protected readonly OmnipediaDateLazyPluginCollection $datePluginCollection;

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
   * The lazy date plug-in configuration.
   *
   * @var array
   */
  protected readonly array $pluginConfig;

  /**
   * Dates that are part of the defined date plug-in configuration.
   *
   * @var string[]
   */
  protected array $definedDates = [
    '2049-09-28',
    '2049-09-29',
    '2049-09-30',
    '2049-10-01',
    '2049-10-02',
    '2049-10-05',
    '2049-10-10',
  ];

  /**
   * Dates that are not part of the defined date plug-in configuration.
   *
   * @var string[]
   */
  protected array $notDefinedDates = [
    '2049-09-20',
    '2049-09-27',
    '2049-10-11',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->stateManager = $this->container->get('state');

    $this->datePluginManager = $this->container->get(
      'plugin.manager.omnipedia_date'
    );

    // Set up the state entry for the plug-in deriver to find and build
    // derivatives from.
    $this->stateManager->set(
      self::DEFINED_DATES_STATE_KEY,
      ['all' => $this->definedDates]
    );

    $pluginConfig = [];

    foreach ($this->definedDates as $date) {
      $pluginConfig[$date] = [
        'id'    => 'date:' . $date,
        'date'  => $date,
      ];
    }

    $this->pluginConfig = $pluginConfig;

    $this->datePluginCollection = new OmnipediaDateLazyPluginCollection(
      $this->datePluginManager, $this->pluginConfig,
    );

  }

  /**
   * Test that the plug-in collection has the expected instance IDs.
   */
  public function testPluginCollectionInstances(): void {

    /** @var string[] */
    $expectedInstanceIds = [];

    foreach ($this->definedDates as $date) {
      $expectedInstanceIds[$date] = $date;
    }

    $this->assertEquals(
      $expectedInstanceIds,
      $this->datePluginCollection->getInstanceIds(),
    );

  }

  /**
   * Test that all of the initialized plug-ins' dates match their plug-in IDs.
   */
  public function testPluginCollectionDates(): void {

    foreach ($this->pluginConfig as $instanceId => $config) {

      $this->assertEquals(
        $config['date'],
        $this->datePluginCollection->get($instanceId)->format('storage'),
      );

    }

  }

}
