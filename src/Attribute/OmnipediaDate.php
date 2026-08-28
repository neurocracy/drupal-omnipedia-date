<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an OmnipediaDate attribute for plug-in discovery.
 *
 * @see \Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface
 *
 * @see plugin_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class OmnipediaDate extends Plugin {

  /**
   * Constructs a Rebuilder attribute.
   *
   * @param string $id
   *   The plug-in ID.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $title
   *   The human readable title of the plug-in.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $description
   *   A brief human readable description of the plug-in.
   *
   * @param class-string|null $deriver
   *   (optional) A deriver class.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $title,
    public readonly TranslatableMarkup $description,
    public readonly ?string $deriver = null,
  ) {}

}
