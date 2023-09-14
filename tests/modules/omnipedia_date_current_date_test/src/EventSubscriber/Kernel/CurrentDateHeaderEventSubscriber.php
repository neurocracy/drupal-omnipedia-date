<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_current_date_test\EventSubscriber\Kernel;

use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Kernel event subscriber to add the current date HTTP header on all responses.
 */
class CurrentDateHeaderEventSubscriber implements EventSubscriberInterface {

  /**
   * Name of the HTTP header added to every response with the current date.
   */
  public const HEADER = 'X-Omnipedia-Current-Date';

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\CurrentDateInterface $currentDate
   *   The Omnipedia current date service.
   */
  public function __construct(
    protected readonly CurrentDateInterface $currentDate,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

  /**
   * Add the current date as a header on all responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Symfony response event object.
   */
  public function onKernelResponse(ResponseEvent $event): void {

    /** @var \Symfony\Component\HttpFoundation\Response */
    $response = $event->getResponse();

    $response->headers->set(self::HEADER, $this->currentDate->get());

  }

}
