<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Service;

use Drupal\Core\State\StateInterface;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The Omnipedia current date service.
 */
class CurrentDate implements CurrentDateInterface {

  /**
   * The Symfony session attribute key where we store the current date.
   *
   * @see https://symfony.com/doc/3.4/components/http_foundation/sessions.html#namespaced-attributes
   */
  protected const CURRENT_DATE_SESSION_KEY = 'omnipedia/currentDate';

  /**
   * The current date as a string.
   *
   * @var string
   */
  protected string $currentDate = '';

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\DateCollectionInterface $dateCollection
   *   The Omnipedia date collection service.
   *
   * @param \Drupal\omnipedia_date\Service\DefaultDateInterface $defaultDate
   *   The Omnipedia default date service.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The Symfony session service.
   */
  public function __construct(
    protected readonly DateCollectionInterface  $dateCollection,
    protected readonly DefaultDateInterface     $defaultDate,
    protected readonly SessionInterface         $session,
  ) {}

 /**
   * Find and set the current date if it hasn't yet been set.
   *
   * @see self::set()
   *   Validates and sets the current date.
   */
  protected function find(): void {

    // Don't do this twice.
    if (!empty($this->currentDate)) {
      return;
    }

    // Retrieve the current date from session storage, if available, falling
    // back to the default date if not found. Note that we have to check if
    // headers have already been sent to avoid Symfony throwing an error.
    if (!\headers_sent() && $this->session->has(
      self::CURRENT_DATE_SESSION_KEY
    )) {
      $date = $this->session->get(self::CURRENT_DATE_SESSION_KEY);

    } else {

      $date = $this->defaultDate->get();

    }

    $this->set($date);

  }

  /**
   * {@inheritdoc}
   */
  public function set(string $date): void {

    $this->currentDate = $this->dateCollection->get($date)->format('storage');

    // Save to session storage if headers haven't been sent yet - checking this
    // is necessary to avoid Symfony throwing an error.
    if (!\headers_sent()) {

      $this->session->set(
        self::CURRENT_DATE_SESSION_KEY,
        $this->currentDate,
      );

    }

  }


  /**
   * {@inheritdoc}
   */
  public function get(): string {

    $this->find();

    return $this->currentDate;

  }

}
