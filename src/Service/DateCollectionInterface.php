<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

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

}
