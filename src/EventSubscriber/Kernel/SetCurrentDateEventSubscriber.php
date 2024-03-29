<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\EventSubscriber\Kernel;

use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_core\Service\WikiNodeRouteInterface;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\typed_entity\EntityWrapperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to update current date if a wiki page node is being viewed.
 *
 * @see \Symfony\Component\HttpKernel\KernelEvents::REQUEST
 *   Subscribes to this event to update the current date as early as possible
 *   if the current route contains a wiki page node in its parameters. The
 *   documentation states that this event is dispatched before "any other code
 *   in the framework is executed", but if this is still not early enough, there
 *   are other events in that class that we could subscribe to if need be.
 */
class SetCurrentDateEventSubscriber implements EventSubscriberInterface {

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $currentRouteMatch
   *   The Drupal current route match service.
   *
   * @param \Drupal\omnipedia_date\Service\CurrentDateInterface $currentDate
   *   The Omnipedia current date service.
   *
   * @param \Drupal\typed_entity\EntityWrapperInterface $typedEntityRepositoryManager
   *   The Typed Entity repository manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeRouteInterface $wikiNodeRoute
   *   The Omnipedia wiki node route service.
   */
  public function __construct(
    protected readonly StackedRouteMatchInterface $currentRouteMatch,
    protected readonly CurrentDateInterface       $currentDate,
    protected readonly EntityWrapperInterface     $typedEntityRepositoryManager,
    protected readonly WikiNodeResolverInterface  $wikiNodeResolver,
    protected readonly WikiNodeRouteInterface     $wikiNodeRoute,
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
   * Update the current date if a wiki page node is found in the route params.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Symfony request event object.
   */
  public function onKernelRequest(RequestEvent $event): void {

    // Bail if this is not a node page to avoid false positives.
    if (!$this->wikiNodeRoute->isWikiNodeViewRouteName(
      $this->currentRouteMatch->getRouteName()
    )) {
      return;
    }

    // If there's a 'node' route parameter, attempt to resolve it to a wiki
    // node. Note that the 'node' parameter is not upcast into a Node object if
    // viewing a (Drupal) revision other than the currently published one.
    /** @var \Drupal\node\NodeInterface|null */
    $node = $this->wikiNodeResolver->resolveNode(
      $this->currentRouteMatch->getParameter('node')
    );

    if (!\is_object($node)) {
      return;
    }

    /** @var \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface */
    $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

    if ($wrappedNode->isWikiNode() === false) {
      return;
    }

    /** @var string|null */
    $currentDate = $wrappedNode->getWikiDate();

    // Bail if the date couldn't be found.
    if ($currentDate === null) {
      return;
    }

    $this->currentDate->set($currentDate);

  }

}
