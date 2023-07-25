<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Core\State\StateInterface;
use Drupal\omnipedia_core\Service\WikiNodeMainPageInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;

/**
 * The Omnipedia default date servivce.
 */
class DefaultDate implements DefaultDateInterface {

  /**
   * The Drupal state key where we store the default date.
   */
  protected const DEFAULT_DATE_STATE_KEY = 'omnipedia.default_date';

  /**
   * The default date as a string.
   *
   * @var string
   */
  protected string $defaultDate = '';

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\DateCollectionInterface $dateCollection
   *   The Omnipedia date collection service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeMainPageInterface $wikiNodeMainPage
   *   The Omnipedia wiki node main page service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    protected readonly DateCollectionInterface    $dateCollection,
    protected readonly WikiNodeMainPageInterface  $wikiNodeMainPage,
    protected readonly WikiNodeResolverInterface  $wikiNodeResolver,
    protected readonly StateInterface             $stateManager,
  ) {}

  /**
   * Find and set the default date if it hasn't yet been set.
   *
   * @see self::setDefaultDate()
   *   Validates and sets the default date.
   *
   * @throws \UnexpectedValueException
   *   Exception thrown when a date cannot be retrieved from the front page
   *   node.
   */
  protected function find(): void {

    // Don't do this twice.
    if (!empty($this->defaultDate)) {
      return;
    }

    /** @var string|null */
    $stateString = $this->stateManager->get(self::DEFAULT_DATE_STATE_KEY);

    // If we got a string instead of null, assume it's a date string, set it,
    // and return.
    if (\is_string($stateString) && !empty($stateString)) {
      $this->set($stateString);

      return;
    }

    // If there's no default date set in the site state, we have to try to infer
    // it from the default front page.

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $defaultMainPage = $this->wikiNodeMainPage->getMainPage('default');

    if (!$this->wikiNodeResolver->isWikiNode($defaultMainPage)) {
      throw new \UnexpectedValueException(
        'The default front page configured in the site settings does not appear to be a wiki page node.',
      );
    }

    /** @var string|null */
    $nodeDate = $defaultMainPage->getWikiNodeDate();

    if ($nodeDate === null) {
      throw new \UnexpectedValueException(
        'Could not read the default date from the default main page node.',
      );
    }

    $this->set($nodeDate);

  }

  /**
   * {@inheritdoc}
   */
  public function set(string $date): void {

    $this->defaultDate = $this->dateCollection->get($date)->format('storage');

    // Save to state storage.
    $this->stateManager->set(
      self::DEFAULT_DATE_STATE_KEY,
      $this->defaultDate,
    );

  }

  /**
   * {@inheritdoc}
   */
  public function get(): string {

    $this->find();

    return $this->defaultDate;

  }

}
