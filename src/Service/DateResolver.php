<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;
use Drupal\omnipedia_date\Service\DateResolverInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\omnipedia_date\Service\DefinedDatesInterface;

/**
 * The Omnipedia date resolver service.
 */
class DateResolver implements DateResolverInterface {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \ Drupal\omnipedia_date\Service\CurrentDateInterface $currentDate
   *   The Omnipedia current date service.
   *
   * @param \Drupal\omnipedia_date\Service\DateCollectionInterface $dateCollection
   *   The Omnipedia date collection service.
   *
   * @param \Drupal\omnipedia_date\Service\DefaultDateInterface $defaultDate
   *   The Omnipedia default date service.
   *
   * @param \Drupal\omnipedia_date\Service\DefinedDatesInterface $definedDates
   *   The Omnipedia defined dates service.
   */
  public function __construct(
    protected readonly CurrentDateInterface     $currentDate,
    protected readonly DateCollectionInterface  $dateCollection,
    protected readonly DefaultDateInterface     $defaultDate,
    protected readonly DefinedDatesInterface    $definedDates,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function resolve(
    string $dateOrKeyword, bool $includeUnpublished = false,
  ): OmnipediaDateInterface {

    if ($dateOrKeyword === 'current') {

      $resolvedDate = $this->currentDate->get();

    } else if ($dateOrKeyword === 'default') {

      $resolvedDate = $this->defaultDate->get();

    } else if ($dateOrKeyword === 'first') {

      $resolvedDate = $this->definedDates->getFirstDate($includeUnpublished);

    } else if ($dateOrKeyword === 'last') {

      $resolvedDate = $this->definedDates->getLastDate($includeUnpublished);

    } else {

      /** @var \Drupal\Component\Datetime\DateTimePlus */
      $dateObject = new DateTimePlus($dateOrKeyword, null);

      if ($dateObject->hasErrors()) {

        throw new \InvalidArgumentException(\sprintf(
          'There were one or more errors attempting to parse "%s" into %s object:%s',
          $dateOrKeyword,
          DateTimePlus::class,
          "\n" . \implode("\n", $dateObject->getErrors()),
        ));

      }

      return $this->dateCollection->getFromDateTimeObject($dateObject);

    }

    return $this->dateCollection->get($resolvedDate);

  }

}
