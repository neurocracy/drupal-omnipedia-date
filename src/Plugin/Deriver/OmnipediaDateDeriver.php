<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Omnipedia Date plug-in deriver.
 *
 * @see \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDate
 *   Deriver for this plug-in.
 */
class OmnipediaDateDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * Constructs this deriver; saves dependencies.
   *
   * @param string $basePluginId
   *   The base plug-in ID this deriver acts on.
   *
   * @param \Drupal\Core\State\StateInterface $stateManager
   *   The Drupal state system manager.
   */
  public function __construct(
    protected readonly string $basePluginId,
    protected readonly StateInterface $stateManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {

    return new static(
      $basePluginId,
      $container->get('state'),
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {

    /** @var array|null */
    $stateData = $this->stateManager->get(self::DEFINED_DATES_STATE_KEY);

    if (!\is_array($stateData) || empty($stateData['all'])) {
      return $this->derivatives;
    }

    $pluginConfig = [];

    foreach ($stateData['all'] as $date) {

      $pluginConfig[$date] = [
        'date' => $date,
      ];

    }

    foreach ($pluginConfig as $key => $config) {

      $this->derivatives[$key] = $basePluginDefinition + $config;

    }

    return $this->derivatives;

  }

}
