<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Entity;

/**
 * Defines a common interface for entities that have a start and end date.
 *
 * @see \Drupal\omnipedia_date\Entity\EntityWithDateRangeTrait
 *   Entities implementing this interface are expected to also use this trait.
 */
interface EntityWithDateRangeInterface {

  /**
   * Get this entity's start date.
   *
   * @return string
   *   Either the stored date string or the string 'first' if not set.
   */
  public function getStartDate(): string;

  /**
   * Get this entity's end date.
   *
   * @return string
   *   Either the stored date string or the string 'last' if not set.
   */
  public function getEndDate(): string;

}
