<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

/**
 * The Omnipedia current date service interface.
 */
interface CurrentDateInterface {

  /**
   * Validate and set the current date.
   *
   * @param string $date
   *   A date string in the storage format.
   */
  public function set(string $date): void;

  /**
   * Get the current date.
   *
   * @return string
   *   The current date as a string in the storage format.
   */
  public function get(): string;

}
