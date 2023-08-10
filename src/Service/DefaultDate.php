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
   * {@inheritdoc}
   */
  public function set(string $date): void {

    $this->stateManager->set(
      self::STATE_KEY, $this->dateCollection->get($date)->format('storage'),
    );

  }

  /**
   * {@inheritdoc}
   *
   * @throws \UnexpectedValueException
   *   If a default date has not been set.
   */
  public function get(): string {

    /** @var string|null */
    $stateString = $this->stateManager->get(self::STATE_KEY);

    if (!\is_string($stateString) || empty($stateString)) {

      throw new \UnexpectedValueException(
        'No default date has been set!',
      );

    }

    return $stateString;

  }

}
