<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Core\State\StateInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;

/**
 * The Omnipedia default date servivce.
 */
class DefaultDate implements DefaultDateInterface {

  /**
   * The Drupal state key where we store the default date.
   */
  protected const STATE_KEY = 'omnipedia.default_date';

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
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    protected readonly DateCollectionInterface  $dateCollection,
    protected readonly StateInterface           $stateManager,
  ) {}

  /**
   * Find the default date if it hasn't yet been set.
   *
   * @see self::set()
   *   Sets the default date.
   *
   * @throws \UnexpectedValueException
   *   If a default date has not been set.
   */
  protected function find(): void {

    // Don't do this twice.
    if (!empty($this->defaultDate)) {
      return;
    }

    /** @var string|null */
    $stateString = $this->stateManager->get(self::STATE_KEY);

    // If we got a string instead of null, assume it's a date string, set it,
    // and return.
    if (\is_string($stateString) && !empty($stateString)) {

      $this->set($stateString);

      return;

    }

    throw new \UnexpectedValueException(
      'No default date has been set!',
    );

  }

  /**
   * {@inheritdoc}
   */
  public function set(string $date): void {

    $this->defaultDate = $this->dateCollection->get($date)->format('storage');

    // Save to state storage.
    $this->stateManager->set(self::STATE_KEY, $this->defaultDate);

  }

  /**
   * {@inheritdoc}
   */
  public function get(): string {

    $this->find();

    return $this->defaultDate;

  }

}
