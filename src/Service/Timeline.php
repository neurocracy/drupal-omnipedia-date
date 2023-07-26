<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;
use Drupal\omnipedia_date\Service\DateResolverInterface;
use Drupal\omnipedia_date\Service\DefinedDatesInterface;
use Drupal\omnipedia_date\Service\TimelineInterface;
use Drupal\omnipedia_date\Value\OmnipediaDateRange;

/**
 * The Omnipedia timeline service.
 */
class Timeline implements TimelineInterface {

  use StringTranslationTrait;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\DateCollectionInterface $dateCollection
   *   The Omnipedia date collection service.
   *
   * @param \Drupal\omnipedia_date\Service\DefaultDateInterface $defaultDate
   *   The Omnipedia default date service.
   *
   * @param \Drupal\omnipedia_date\Service\DateResolverInterface $dateResolver
   *   The Omnipedia date resolver servivce.
   *
   * @param \Drupal\omnipedia_date\Service\DefinedDatesInterface $definedDates
   *   The Omnipedia defined dates service.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    protected readonly DateCollectionInterface  $dateCollection,
    protected readonly DateResolverInterface    $dateResolver,
    protected readonly DefaultDateInterface     $defaultDate,
    protected readonly DefinedDatesInterface    $definedDates,
    protected $stringTranslation,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function setDefaultDate(string $date): void {

    $this->defaultDate->set($date);

  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedDates(bool $includeUnpublished = false): array {

    return $this->definedDates->get($includeUnpublished);

  }

  /**
   * Convenience method to get a date object or resolve a string date/keyword.
   *
   * @param string|\Drupal\Component\Datetime\DateTimePlus $dateOrKeyword
   *   Must be one of:
   *
   *   - A string date or keyword that can be resolved by
   *     \Drupal\omnipedia_date\Service\DateResolverInterface::resolve().
   *
   *   - An instance of \Drupal\Component\Datetime\DateTimePlus.
   *
   * @param bool $includeUnpublished
   *   Whether to include dates that have only unpublished content.
   *
   * @return \Drupal\Component\Datetime\DateTimePlus
   *   A date object representing $date. If $date was provided as a date object,
   *   it will be returned as-is.
   *
   * @see \Drupal\omnipedia_date\Service\DateResolverInterface::resolve()
   */
  protected function getDateObject(
    string|DateTimePlus $dateOrKeyword, bool $includeUnpublished = false
  ): DateTimePlus {

    if ($dateOrKeyword instanceof DateTimePlus) {
      return $dateOrKeyword;
    }

    return $this->dateResolver->resolve(
      $dateOrKeyword, $includeUnpublished
    )->getDateObject();

  }

  /**
   * {@inheritdoc}
   */
  public function getDateFormatted(
    string|DateTimePlus $dateOrKeyword = 'current', string $format = 'long'
  ): string|TranslatableMarkup {

    if ($dateOrKeyword === 'first') {
      return $this->t('First date');

    } else if ($dateOrKeyword === 'last') {
      return $this->t('Last date');

    } else if ($dateOrKeyword instanceof DateTimePlus) {

      /** @var \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface */
      $instance = $this->dateCollection->getFromDateTimeObject($dateOrKeyword);

    } else {

      /** @var \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface */
      $instance = $this->dateResolver->resolve($dateOrKeyword);

    }

    return $instance->format($format);

  }

  /**
   * {@inheritdoc}
   */
  public function isDateBetween(
    string $date,
    string $startDate,
    string $endDate,
    bool $includeUnpublished = false
  ): bool {

    if (empty($date)) {
      return true;
    }

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $dateObject = $this->getDateObject($date, $includeUnpublished);

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $startDateObject = $this->getDateObject($startDate, $includeUnpublished);

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $endDateObject = $this->getDateObject($endDate, $includeUnpublished);

    return (new OmnipediaDateRange(
      $startDateObject, $endDateObject,
    ))->overlapsDate($dateObject);

  }

  /**
   * {@inheritdoc}
   */
  public function doDateRangesOverlap(
    string $startDate1,
    string $endDate1,
    string $startDate2,
    string $endDate2,
    bool $includeUnpublished = false
  ): bool {

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $startDate1Object = $this->getDateObject(
      $startDate1, $includeUnpublished
    );

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $endDate1Object = $this->getDateObject(
      $endDate1, $includeUnpublished
    );

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $startDate2Object = $this->getDateObject(
      $startDate2, $includeUnpublished
    );

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $endDate2Object = $this->getDateObject(
      $endDate2, $includeUnpublished
    );

    return (new OmnipediaDateRange(
      $startDate1Object, $endDate1Object,
    ))->overlapsWithRange(new OmnipediaDateRange(
      $startDate2Object, $endDate2Object,
    ));

  }

}
