<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date_refreshless\Hooks;

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\hux\Attribute\Alter;
use Drupal\hux\Attribute\Hook;
use Drupal\omnipedia_date\Service\CurrentDateInterface;
use Drupal\omnipedia_date_refreshless\OmnipediaDateSettingsInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * JavaScript hook implementations.
 */
class Javascript {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\CurrentDateInterface $currentDate
   *   The Omnipedia current date service.
   */
  public function __construct(
    #[Autowire(service: 'omnipedia_date.current_date')]
    protected readonly CurrentDateInterface $currentDate,
  ) {}

  #[Hook('js_settings_build')]
  /**
   * Output set current date header to drupalSettings.
   *
   * Note that this is cached.
   */
  public function outputHeaderName(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    $settings['omnipedia'][
      'setDateHeaderName'
    ] = OmnipediaDateSettingsInterface::SET_DATE_HEADER_NAME;

  }

  #[Alter('js_settings')]
  /**
   * Output the current date drupalSettings value.
   *
   * Note that js_settings_build is cached, but js_settings_alter is not, so
   * we have to use the latter. This would ideally be cached and vary by the
   * wiki date, but that's not currently possible because the asset resolver
   * doesn't use the render cache nor a variation cache, so cache contexts are
   * not supported here.
   *
   * @see \Drupal\Core\Asset\AssetResolver::getJsAssets()
   */
  public function outputCurrentDate(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    $settings['omnipedia']['currentDate'] = $this->currentDate->get();

  }

}
