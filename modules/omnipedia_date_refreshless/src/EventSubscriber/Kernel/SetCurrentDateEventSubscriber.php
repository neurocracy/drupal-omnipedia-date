<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_refreshless\EventSubscriber\Kernel;

use Drupal\omnipedia_date\EventSubscriber\Kernel\SetCurrentDateEventSubscriber as DecoratedEventSubscriber;
use Drupal\refreshless\Service\RequestWrapperFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetCurrentDateEventSubscriber implements EventSubscriberInterface {

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

    $requestWrapper = $this->requestWrapperFactory->fromRequest();

    if ($requestWrapper->isRefreshless() === true && (
      $requestWrapper->isPrefetch() === true ||
      $requestWrapper->isPreload() === true
    )) {
      return;
    }

    $this->decorated->onKernelRequest($event);

  }

}
