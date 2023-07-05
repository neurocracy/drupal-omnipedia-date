<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\PluginCollection;

use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * The Omnipedia Date lazy plug-in collection.
 */
class OmnipediaDateLazyPluginCollection extends DefaultLazyPluginCollection {

  /**
   * The key within the plugin configuration that contains the plugin ID.
   *
   * @var string
   */
  protected $pluginKey = 'date';

}
