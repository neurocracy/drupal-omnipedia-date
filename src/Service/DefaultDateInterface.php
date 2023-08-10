<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

/**
 * The Omnipedia default date service interface.
 */
interface DefaultDateInterface {

  /**
   * Validate and set the default date.
   *
   * @param string $date
   *   A date string in the storage format.
   */
  public function set(string $date): void;

  /**
   * Get the default date.
   *
   * @return string
   *   The default date as a string in the storage format.
   */
  public function get(): string;

}
