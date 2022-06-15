<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\EventSubscriber\Config;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeStorageInterface;
use Drupal\omnipedia_date\Service\TimelineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to update the default date when config is updated.
 */
class UpdateDefaultDateEventSubscriber implements EventSubscriberInterface {

  /**
   * The Drupal node entity storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * The Omnipedia timeline service.
   *
   * @var \Drupal\omnipedia_date\Service\TimelineInterface
   */
  protected TimelineInterface $timeline;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type manager.
   *
   * @param \Drupal\omnipedia_date\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   */
  public function __construct(
    EntityTypeManagerInterface  $entityTypeManager,
    TimelineInterface           $timeline
  ) {
    $this->nodeStorage  = $entityTypeManager->getStorage('node');
    $this->timeline     = $timeline;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigSave',
    ];
  }

  /**
   * This updates the stored default date when system.site is updated.
   *
   * Note that this has been successfully tested with configuration import via
   * Drush, but may not work as expected if system.site is changed via a
   * hook_update_N() hook.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Drupal configuration CRUD event object.
   *
   * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Extension%21module.api.php/function/hook_update_N
   *   Documentation for hook_update_N() describing the limitations of what is
   *   safe during that hook. Unclear if the state manager is considered a safe
   *   operation.
   *
   * @todo Since this isn't intended to be run via a hook_update_N(), is there
   *   some sort of check we can have to bail if this is invoked during a
   *   hook_update_N()?
   */
  public function onConfigSave(ConfigCrudEvent $event): void {

    // Bail if this wasn't the system.site config that changed or this is
    // system.site but the page.front key hasn't been changed.
    if (
      $event->getConfig()->getName() !== 'system.site' ||
      !$event->isChanged('page.front')
    ) {
      return;
    }

    /** @var \Drupal\Core\Url */
    $url = Url::fromUserInput($event->getConfig()->get('page.front'));

    if (!$url->isRouted()) {
      return;
    }

    /** @var array */
    $parameters = $url->getRouteParameters();

    if (!isset($parameters['node'])) {
      return;
    }

    /** @var \Drupal\omnipedia_core\Entity\NodeInterface|null */
    $node = $this->nodeStorage->load($parameters['node']);

    if (!\is_object($node)) {
      return;
    }

    // Update the default date with the new default main page's date.
    $this->timeline->setDefaultDate($node->getWikiNodeDate());

  }

}
