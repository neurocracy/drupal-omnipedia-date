<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;

/**
 * The Omnipedia date collection service interface.
 */
interface DateCollectionInterface {

  /**
   * Get the Omnipedia date plug-in instance for the specified date.
   *
   * @param string $date
   *
   * @return \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface
   */
  public function get(string $date): OmnipediaDateInterface;

  /**
   * Get the Omnipedia date plug-in instance given a DrupalDateTime object.
   *
   * Note that the DrupalDateTime object provided will only be used as the input
   * date; the returned Omnipedia date plug-in is likely to wrap a different
   * DrupalDateTime instance unless the provided DrupalDateTime was returned
   * from an existing Omnipedia date plug-in matching that date.
   *
   * This is primarily intended to be used in situations where a DrupalDateTime
   * object is already instantiated and provided, such as in date field
   * formatters.
   *
   * @param Drupal\Core\Datetime\DrupalDateTime $dateTime
   *   A DrupalDateTime object instance to fetch the date from.
   *
   * @return \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface
   *   An Omnipedia date object instance.
   */
  public function getFromDrupalDateTime(
    DrupalDateTime $dateTime
  ): OmnipediaDateInterface;

}
