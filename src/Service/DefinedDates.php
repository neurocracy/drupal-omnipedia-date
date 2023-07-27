<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Core\State\StateInterface;
use Drupal\omnipedia_core\Service\WikiNodeTrackerInterface;
use Drupal\omnipedia_date\Service\DefinedDatesInterface;

/**
 * The Omnipedia defined dates service.
 */
class DefinedDates implements DefinedDatesInterface {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   *
   * @see self::find()
   *   Used by this to save dates to state storage.
   *
   * @see self::get()
   *   Used by this to read dates from state storage.
   */
  protected const STATE_KEY = 'omnipedia.defined_dates';

  /**
   * Dates defined by content.
   *
   * Two versions are stored, under the top level keys 'all' (published and
   * unpublished content) and 'published' (only published content). Each top
   * level key is an array of date strings in the 'storage' format.
   *
   * @var array
   *
   * @see self::find()
   *   Scans content to build arrays of dates.
   *
   * @see self::get()
   *   Use this to get these dates.
   *
   * @see self::STATE_KEY
   *   The Drupal state key where we store the list of dates defined by content.
   */
  protected array $definedDates;

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeTrackerInterface $wikiNodeTracker
   *   The Omnipedia wiki node tracker service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    protected readonly WikiNodeTrackerInterface $wikiNodeTracker,
    protected readonly StateInterface           $stateManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function find(): void {

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
    $this->stateManager->set(self::STATE_KEY, $dates);

    // Save to our property for quick retrieval within this request.
    $this->definedDates = $dates;

  }

  /**
   * {@inheritdoc}
   */
  public function get(bool $includeUnpublished = false): array {

    /** @var string */
    $dateTypeKey = $includeUnpublished ? 'all' : 'published';

    // If we've already saved the defined dates to the property, return that.
    if (isset($this->definedDates[$dateTypeKey])) {
      return $this->definedDates[$dateTypeKey];
    }

    // Attempt to load defined dates from state storage.
    /** @var array|null */
    $stateData = $this->stateManager->get(self::STATE_KEY);

    // If state storage returned an array instead of null, save it to the
    // property and return the appropriate data.
    if (is_array($stateData)) {

      $this->definedDates = $stateData;

      return $this->definedDates[$dateTypeKey];

    }

    // If neither the property nor the state data are set, scan content to find
    // and save the defined dates.
    $this->find();

    return $this->definedDates[$dateTypeKey];

  }

  /**
   * {@inheritdoc}
   */
  public function getFirstDate(bool $includeUnpublished = false): string {

    $dates = $this->get($includeUnpublished);

    /** @var string|bool */
    $date = \reset($dates);

    if ($date === false) {
      throw new \UnexpectedValueException(
        'Cannot get the first date because no dates are available!'
      );
    }

    return $date;

  }

  /**
   * {@inheritdoc}
   */
  public function getLastDate(bool $includeUnpublished = false): string {

    $dates = $this->get($includeUnpublished);

    $date = \end($dates);

    if ($date === false) {
      throw new \UnexpectedValueException(
        'Cannot get the last date because no dates are available!'
      );
    }

    return $date;

  }

}
