<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_refreshless;

/**
 * Shared Omnipedia date settings interface.
 */
interface OmnipediaDateSettingsInterface {

  /**
   * Name of the header the front-end is expected to send to set current date.
   */
  public const SET_DATE_HEADER_NAME = 'X-Omnipedia-Set-Current-Date';

}
