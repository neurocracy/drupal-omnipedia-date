<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_refreshless\EventSubscriber\Kernel;

use Drupal\omnipedia_date\EventSubscriber\Kernel\SetCurrentDateEventSubscriber as DecoratedEventSubscriber;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date\Service\DateResolverInterface;
use Drupal\omnipedia_date\Service\DefinedDatesInterface;
use Drupal\refreshless\Service\RequestWrapperFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetCurrentDateEventSubscriber implements EventSubscriberInterface {

  protected const DATE_HEADER_NAME = 'X-Omnipedia-Set-Current-Date';

  /**
   * Service constructor; saves dependencies.
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

  public function onKernelRequest(RequestEvent $event): void {

    $request = $event->getRequest();

    $requestWrapper = $this->requestWrapperFactory->fromRequest($request);

    if (
      $requestWrapper->isRefreshless() === false ||
      $request->headers->has(self::DATE_HEADER_NAME) === false
    ) {

      $this->decorated->onKernelRequest($event);

      return;

    }

    $date = $this->dateResolver->resolve(
      $request->headers->get(self::DATE_HEADER_NAME),
    );

    $definedDates = $this->definedDates->get();

    if (!in_array($date->format('storage'), $definedDates)) {
      return;
    }

    \Drupal::logger('omnipedia_date_refreshless')->debug(
      'Set current date from header: ' . $date->format('storage'),
    );

    $this->currentDate->set($date->format('storage'));

  }

}
