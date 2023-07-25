<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\omnipedia_date\PluginCollection\OmnipediaDateLazyPluginCollection;
use Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * The Omnipedia date collection service.
 */
class DateCollection implements DateCollectionInterface {

  /**
   * The Omnipedia Date lazy plug-in collection.
   *
   * @var \Drupal\omnipedia_date\PluginCollection\OmnipediaDateLazyPluginCollection
   */
  protected readonly OmnipediaDateLazyPluginCollection $datePluginCollection;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface $datePluginManager
   *   The Omnipedia Date plug-in manager.
   */
  public function __construct(
    protected readonly OmnipediaDateManagerInterface $datePluginManager,
  ) {

    $this->datePluginCollection = new OmnipediaDateLazyPluginCollection(
      $this->datePluginManager
    );

  }

  /**
   * {@inheritdoc}
   */
  public function get(string $date): OmnipediaDateInterface {

    // @todo Remove this once edge cases are resolved where the deriver hasn't
    //   created the expected dates.
    if (!$this->datePluginCollection->has($date)) {

      $this->datePluginCollection->addInstanceId($date, [
        'id'    => 'date:' . $date,
        'date'  => $date,
      ]);

    }

    return $this->datePluginCollection->get($date);

  }

  /**
   * {@inheritdoc}
   */
  public function getFromDateTimeObject(
    DateTimePlus $dateTime
  ): OmnipediaDateInterface {

    $date = $dateTime->format(OmnipediaDateInterface::DATE_FORMAT_STORAGE);

    return $this->get($date);

  }

}
