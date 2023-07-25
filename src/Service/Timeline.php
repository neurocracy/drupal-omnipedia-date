<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;
use Drupal\omnipedia_date\Service\TimelineInterface;
use Drupal\omnipedia_date\Value\OmnipediaDateRange;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia timeline service.
 */
class Timeline implements TimelineInterface {

  use StringTranslationTrait;

  /**
   * The Symfony session attribute key where we store the current date.
   *
   * @see https://symfony.com/doc/3.4/components/http_foundation/sessions.html#namespaced-attributes
   */
  protected const CURRENT_DATE_SESSION_KEY = 'omnipedia/currentDate';

  /**
   * The Drupal state key where we store the default date.
   */
  protected const DEFAULT_DATE_STATE_KEY = 'omnipedia.default_date';

  /**
   * The Drupal state key where we store the list of dates defined by content.
   *
   * @see $this->findDefinedDates()
   *   Uses this constant to save dates to state storage.
   *
   * @see $this->getDefinedDates()
   *   Uses this constant to read dates from state storage.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The current date as a string.
   *
   * @var string
   */
  protected string $currentDate = '';

  /**
   * The default date as a string.
   *
   * @var string
   */
  protected string $defaultDate = '';

  /**
   * Dates defined by content.
   *
   * Two versions are stored, under the top level keys 'all' (published and
   * unpublished content) and 'published' (only published content). Each top
   * level key is an array of date strings in the 'storage' format.
   *
   * @var array
   *
   * @see $this->findDefinedDates()
   *   Scans content to build arrays of dates.
   *
   * @see $this->getDefinedDates()
   *   Use this to get these dates.
   *
   * @see self::DEFINED_DATES_STATE_KEY
   *   Drupal state key where dates are stored persistently between requests.
   */
  protected array $definedDates;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface $wikiNodeMainPage
   *   The Omnipedia wiki node main page service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   *
   * @param \Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface $datePluginManager
   *   The Omnipedia Date plug-in manager.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    protected readonly DateCollectionInterface    $dateCollection,
    protected readonly WikiNodeMainPageInterface $wikiNodeMainPage,
    protected readonly WikiNodeResolverInterface $wikiNodeResolver,
    protected readonly WikiNodeTrackerInterface  $wikiNodeTracker,
    protected readonly SessionInterface          $session,
    protected readonly StateInterface            $stateManager,
    protected $stringTranslation,
  ) {}

  /**
   * Find and set the current date if it hasn't yet been set.
   *
   * @see $this->setCurrentDate()
   *   Validates and sets the current date.
   */
  protected function findCurrentDate(): void {
    // Don't do this twice.
    if (!empty($this->currentDate)) {
      return;
    }

    // Retrieve the current date from session storage, if available, falling
    // back to the default date if not found. Note that we have to check if
    // headers have already been sent to avoid Symfony throwing an error.
    if (!\headers_sent() && $this->session->has(self::CURRENT_DATE_SESSION_KEY)) {
      $date = $this->session->get(self::CURRENT_DATE_SESSION_KEY);

    } else {
      $this->findDefaultDate();

      $date = $this->defaultDate;
    }

    $this->setCurrentDate($date);
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentDate(string $date): void {

    $this->currentDate = $this->dateCollection->get($date)->format('storage');

    // Save to session storage if headers haven't been sent yet - checking this
    // is necessary to avoid Symfony throwing an error.
    if (!\headers_sent()) {
      $this->session->set(
        self::CURRENT_DATE_SESSION_KEY,
        $this->currentDate
      );
    }

  }

  /**
   * Find and set the default date if it hasn't yet been set.
   *
   * @see $this->setDefaultDate()
   *   Validates and sets the default date.
   *
   * @throws \UnexpectedValueException
   *   Exception thrown when a date cannot be retrieved from the front page
   *   node.
   */
  protected function findDefaultDate(): void {
    // Don't do this twice.
    if (!empty($this->defaultDate)) {
      return;
    }

    /** @var string|null */
    $stateString = $this->stateManager->get(self::DEFAULT_DATE_STATE_KEY);

    // If we got a string instead of null, assume it's a date string, set it,
    // and return.
    if (\is_string($stateString) && !empty($stateString)) {
      $this->setDefaultDate($stateString);

      return;
    }

    // If there's no default date set in the site state, we have to try to infer
    // it from the default front page.

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $defaultMainPage = $this->wikiNodeMainPage->getMainPage('default');

    if (!$this->wikiNodeResolver->isWikiNode($defaultMainPage)) {
      throw new \UnexpectedValueException(
        'The default front page configured in the site settings does not appear to be a wiki page node.'
      );
    }

    /** @var string|null */
    $nodeDate = $defaultMainPage->getWikiNodeDate();

    if ($nodeDate === null) {
      throw new \UnexpectedValueException(
        'Could not read the default date from the default main page node.'
      );
    }

    $this->setDefaultDate($nodeDate);
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultDate(string $date): void {

    $this->defaultDate = $this->dateCollection->get($date)->format('storage');

    // Save to state storage.
    $this->stateManager->set(
      self::DEFAULT_DATE_STATE_KEY,
      $this->defaultDate
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getDateObject(
    string|DateTimePlus $date = 'current', bool $includeUnpublished = false
  ): DateTimePlus {
    if (\is_string($date)) {
      if ($date === 'current') {

        $this->findCurrentDate();

        return $this->dateCollection->get(
          $this->currentDate
        )->getDateObject();

      } else if ($date === 'default') {

        $this->findDefaultDate();

        return $this->dateCollection->get(
          $this->defaultDate
        )->getDateObject();

      } else if ($date === 'first' || $date === 'last') {
        /** @var array */
        $definedDates = $this->getDefinedDates($includeUnpublished);

        if ($date === 'first') {
          $date = $definedDates[0];

        } else if ($date === 'last') {
          $date = \end($definedDates);
        }
      }

      return $this->dateCollection->get($date)->getDateObject();

    } else if ($date instanceof DateTimePlus) {
      if ($date->hasErrors()) {
        throw new \InvalidArgumentException(
          'There are one or more errors with the provided \Drupal\Component\Datetime\DateTimePlus object:' .
          "\n" . \implode("\n", $date->getErrors())
        );
      }

      return $date;

    } else {
      throw new \InvalidArgumentException('The $date parameter must either be a string or an instance of \Drupal\Component\Datetime\DateTimePlus.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDateFormatted(
    string|DateTimePlus $date = 'current', string $format = 'long'
  ): string|TranslatableMarkup {

    if ($date === 'first') {
      return $this->t('First date');

    } else if ($date === 'last') {
      return $this->t('Last date');

    } else if ($date === 'current') {

      $this->findCurrentDate();

      $date = $this->currentDate;

    } else if ($date === 'default') {

      $this->findDefaultDate();

      $date = $this->defaultDate;

    }

    if ($date instanceof DateTimePlus) {

      /** @var \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface */
      $instance = $this->dateCollection->getFromDateTimeObject($date);

    } else {

      /** @var \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface */
      $instance = $this->dateCollection->get($date);

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

  /**
   * {@inheritdoc}
   */
  public function findDefinedDates(): void {
    // This defines the keys used to store dates, while the values determine if
    // the key should include unpublished wiki nodes.
    /** @var array */
    $dateTypes = [
      'all'       => true,
      'published' => false
    ];

    /** @var array */
    $dates = [];

    /** @var array */
    $nodeData = $this->wikiNodeTracker->getTrackedWikiNodeData();

    foreach ($dateTypes as $dateType => $includeUnpublished) {
      // Make sure each date type has an array, to avoid errors if no results
      // are found.
      $dates[$dateType] = [];

      foreach ($nodeData['dates'] as $date => $nodesForDate) {
        // If we're including unpublished nodes, add the date unconditionally.
        if ($includeUnpublished === true) {
          $dates[$dateType][] = $date;

        // If we're not including unpublished nodes, we have to check that at
        // least one published node has this date before adding it.
        } else {
          foreach ($nodesForDate as $nid) {
            if ($nodeData['nodes'][$nid]['published'] === true) {
              $dates[$dateType][] = $date;

              break;
            }
          }
        }
      }
    }

    // Save to state storage for retrieval in a future response.
    $this->stateManager->set(
      self::DEFINED_DATES_STATE_KEY,
      $dates
    );

    // Save to our property for quick retrieval within this request.
    $this->definedDates = $dates;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedDates(bool $includeUnpublished = false): array {
    /** @var string */
    $dateTypeKey = $includeUnpublished ? 'all' : 'published';

    // If we've already saved the defined dates to the property, return that.
    if (isset($this->definedDates[$dateTypeKey])) {
      return $this->definedDates[$dateTypeKey];
    }

    // Attempt to load defined dates from state storage.
    /** @var array|null */
    $stateData = $this->stateManager->get(self::DEFINED_DATES_STATE_KEY);

    // If state storage returned an array instead of null, save it to the
    // property and return the appropriate data.
    if (is_array($stateData)) {
      $this->definedDates = $stateData;

      return $this->definedDates[$dateTypeKey];
    }

    // If neither the property nor the state data are set, scan content to find
    // and save the defined dates.
    $this->findDefinedDates();

    return $this->definedDates[$dateTypeKey];
  }

}
