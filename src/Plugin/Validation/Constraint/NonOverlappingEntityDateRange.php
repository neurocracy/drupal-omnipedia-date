<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that an entity's date range doesn't overlap another.
 *
 * @Constraint(
 *   id     = "NonOverlappingEntityDateRange",
 *   label  = @Translation("Non-overlapping date range", context = "Validation"),
 *   type   = "string"
 * )
 */
class NonOverlappingEntityDateRange extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'The date range overlaps with <a href=":entityUrl">%entityLabel</a> (%startDate to %endDate).';

}
