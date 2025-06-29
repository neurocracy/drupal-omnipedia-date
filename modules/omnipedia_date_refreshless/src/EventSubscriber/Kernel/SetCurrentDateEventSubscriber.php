<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_refreshless\EventSubscriber\Kernel;

use Drupal\omnipedia_date\EventSubscriber\Kernel\SetCurrentDateEventSubscriber as DecoratedEventSubscriber;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\refreshless\Service\RequestWrapperFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetCurrentDateEventSubscriber implements EventSubscriberInterface {

  protected string $previousCurrentDate;

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
      KernelEvents::TERMINATE => 'onKernelTerminate',
    ];
  }

  public function onKernelRequest(RequestEvent $event): void {

    $requestWrapper = $this->requestWrapperFactory->fromRequest(
      $event->getRequest(),
    );

    if ($requestWrapper->isRefreshless() === true && (
      $requestWrapper->isPrefetch() === true ||
      $requestWrapper->isPreload() === true
    )) {
      $this->previousCurrentDate = $this->currentDate->get();
    }

    $this->decorated->onKernelRequest($event);

  }

  public function onKernelTerminate(TerminateEvent $event): void {

    if (isset($this->previousCurrentDate)) {

      $this->currentDate->set($this->previousCurrentDate);

      \Drupal::logger('omnipedia_date_refreshless')->debug(
        'Restored current date: ' . $this->previousCurrentDate,
      );

    }

  }

}
