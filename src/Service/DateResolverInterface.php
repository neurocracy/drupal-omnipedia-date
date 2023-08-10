<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;

/**
 * The Omnipedia date resolver service interface.
 */
interface DateResolverInterface {

  /**
   * Resolve a date or keyword to an explicit date string.
   *
   * @param string $dateOrKeyword
   *   Must be one of:
   *
   *   - 'current': Indicates the current date is to be used. This is the
   *     default.
   *
   *   - 'default': Indicates the default date is to be used.
   *
   *   - 'first': Indicates that the first defined date is to be used.
   *
   *   - 'last': Indicates that the last defined date is to be used.
   *
   *   - A string that can be parsed by \Drupal\Component\Datetime\DateTimePlus
   *     without errors.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. This is used
   *   if $date is 'first' or 'last'. Defaults to false.
   *
   * @return \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface
   *   The Omnipedia date plug-in for the resolved date.
   *
   * @throws \InvalidArgumentException
   *   When $dateOrKeyword does not match a recognized keyword and cannot be
   *   parsed into a valid date object.
   */
  public function resolve(
    string $dateOrKeyword, bool $includeUnpublished = false,
  ): OmnipediaDateInterface;

}
