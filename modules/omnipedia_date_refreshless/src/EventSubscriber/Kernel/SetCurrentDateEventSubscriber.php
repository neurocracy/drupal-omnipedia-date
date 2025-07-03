<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_refreshless\EventSubscriber\Kernel;

use Drupal\omnipedia_date\EventSubscriber\Kernel\SetCurrentDateEventSubscriber as DecoratedEventSubscriber;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DateResolverInterface;
use Drupal\omnipedia_date\Service\DefinedDatesInterface;
use Drupal\omnipedia_date_refreshless\OmnipediaDateSettingsInterface;
use Drupal\refreshless\Service\RequestWrapperFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to update the current date if header sent via RefreshLess.
 */
class SetCurrentDateEventSubscriber implements EventSubscriberInterface {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\CurrentDateInterface $currentDate
   *   The Omnipedia current date service.
   *
   * @param \Drupal\omnipedia_date\Service\DateResolverInterface $dateResolver
   *   The Omnipedia date resolver service.
   *
   * @param \Drupal\omnipedia_date\Service\DefinedDatesInterface $definedDates
   *   The Omnipedia defined dates service.
   *
   * @param \Drupal\omnipedia_date\EventSubscriber\Kernel\SetCurrentDateEventSubscriber $decorated
   *   The event subscriber that we decorate.
   *
   * @param \Drupal\refreshless\Service\RequestWrapperFactoryInterface $requestWrapperFactory
   *   The RefreshLess request wrapper factory.
   */
  public function __construct(
    #[Autowire(service: 'omnipedia_date.current_date')]
    protected readonly CurrentDateInterface $currentDate,
    #[Autowire(service: 'omnipedia_date.date_resolver')]
    protected readonly DateResolverInterface $dateResolver,
    #[Autowire(service: 'omnipedia_date.defined_dates')]
    protected readonly DefinedDatesInterface $definedDates,
    #[AutowireDecorated]
    protected readonly DecoratedEventSubscriber $decorated,
    protected readonly RequestWrapperFactoryInterface $requestWrapperFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => 'onKernelRequest',
    ];
  }

  /**
   * Update the current date if our header is sent with a RefreshLess request.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Symfony request event object.
   */
  public function onKernelRequest(RequestEvent $event): void {

    $request = $event->getRequest();

    $requestWrapper = $this->requestWrapperFactory->fromRequest($request);

    if (
      $requestWrapper->isRefreshless() === false ||
      $request->headers->has(
        OmnipediaDateSettingsInterface::SET_DATE_HEADER_NAME,
      ) === false
    ) {

      $this->decorated->onKernelRequest($event);

      return;

    }

    // This validates and parses the date format. Will throw an exception if
    // that fails.
    $date = $this->dateResolver->resolve($request->headers->get(
      OmnipediaDateSettingsInterface::SET_DATE_HEADER_NAME,
    ));

    $definedDates = $this->definedDates->get();

    // Ensure we only set a date that's already defined.
    if (!in_array($date->format('storage'), $definedDates)) {
      return;
    }

    $this->currentDate->set($date->format('storage'));

  }

}
