<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

/**
 * The Omnipedia defined dates service interface.
 */
interface DefinedDatesInterface {

  /**
   * Find all dates defined by content.
   *
   * Note that this method always rebuilds the lists of dates when invoked so it
   * should only be used when necessary, i.e. content has been updated.
   *
   * Once the dates have been found and saved, they can be accessed via
   * self::getDefinedDates().
   *
   * @see self::getDefinedDates()
   *   Returns any defined dates.
   */
  public function find(): void;

  /**
   * Get a list of dates that have content.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. Defaults to
   *   false.
   *
   * @return array
   *   Zero or more unique dates that have content. Note that this will likely
   *   vary based on the $includeUnpublished parameter.
   */
  public function get(bool $includeUnpublished = false): array;

  /**
   * Get the first date defined by content.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. Defaults to
   *   false.
   *
   * @return string
   *   The first defined date, in the storage format. May vary based on the
   *   $includeUnpublished parameter.
   *
   * @throws \UnexpectedValueException
   *   If the list of defined dates is empty.
   */
  public function getFirstDate(bool $includeUnpublished = false): string;

  /**
   * Get the last date defined by content.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content. Defaults to
   *   false.
   *
   * @return string
   *   The last defined date, in the storage format. May vary based on the
   *   $includeUnpublished parameter.
   *
   * @throws \UnexpectedValueException
   *   If the list of defined dates is empty.
   */
  public function getLastDate(bool $includeUnpublished = false): string;

}
