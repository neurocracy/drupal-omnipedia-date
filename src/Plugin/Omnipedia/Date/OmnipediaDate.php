<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Omnipedia\Date;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Plugin\PluginBase;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;

/**
 * The Omnipedia Date plug-in.
 *
 * @OmnipediaDate(
 *   id           = "date",
 *   title        = @Translation("Omnipedia Date"),
 *   description  = @Translation("The Omnipedia Date plug-in."),
 *   deriver      = "Drupal\omnipedia_date\Plugin\Deriver\OmnipediaDateDeriver"
 * )
 */
class OmnipediaDate extends PluginBase implements OmnipediaDateInterface {

  /**
   * The DateTimePlus object instance this plug-in wraps.
   *
   * @var \Drupal\Component\Datetime\DateTimePlus
   */
  protected readonly DateTimePlus $dateObject;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plug-in instance.
   *   This must contain a 'date' key with a string that can be used to build a
   *   DateTimePlus object with.
   *
   * @param string $pluginId
   *   The plugin_id for the plug-in instance.
   *
   * @param array $pluginDefinition
   *   The plug-in implementation definition. PluginBase defines this as mixed,
   *   but we should always have an array so the type is specified.
   */
  public function __construct(
    array $configuration, string $pluginId, array $pluginDefinition
  ) {

    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->initializeDateObject($configuration['date']);

  }

  /**
   * Initialize the DateTimePlus object for this plug-in instance.
   *
   * @param string $date
   *
   * @throws \InvalidArgumentException
   *   If the DateTimePlus object reports any errors.
   */
  protected function initializeDateObject(string $date): void {

    /** @var \Drupal\Component\Datetime\DateTimePlus */
    $dateObject = DateTimePlus::createFromFormat(
      self::DATE_FORMAT_STORAGE,
      $date,
      null,
    );

    if ($dateObject->hasErrors()) {
      throw new \InvalidArgumentException(
        'There were one or more errors in constructing a \Drupal\Component\Datetime\DateTimePlus object:' .
        "\n" . \implode("\n", $dateObject->getErrors())
      );
    }

    $this->dateObject = $dateObject;

  }

  /**
   * {@inheritdoc}
   */
  public function getDateObject(): DateTimePlus {
    return $this->dateObject;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   *   If $format is not one of the expected values.
   *
   * @todo Should this even throw an exception? What about just passing $format
   *   to DateTimePlus::format() if it's not one of our keywords instead?
   */
  public function format(string $format): string {

    switch ($format) {

      case 'storage':

        $formatString = self::DATE_FORMAT_STORAGE;

        break;

      case 'html':

        $formatString = self::DATE_FORMAT_HTML;

        break;

      case 'long':

        $formatString = self::DATE_FORMAT_LONG;

        break;

      case 'short':

        $formatString = self::DATE_FORMAT_SHORT;

        break;

      default:

        throw new \InvalidArgumentException(
          'The $format parameter must be one of "storage", "html", "long", or "short".'
        );

    }

    return $this->dateObject->format($formatString);

  }

}
