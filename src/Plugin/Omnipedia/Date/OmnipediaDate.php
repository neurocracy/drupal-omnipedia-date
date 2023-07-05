<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Omnipedia\Date;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;

/**
 * The Omnipedia Date plug-in.
 *
 * @OmnipediaAttachedData(
 *   id           = "date",
 *   title        = @Translation("Omnipedia Date"),
 *   description  = @Translation("The Omnipedia Date plug-in.")
 * )
 */
class OmnipediaDate extends PluginBase implements OmnipediaDateInterface {

  /**
   * The DrupalDateTime object instance this plug-in wraps.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected DrupalDateTime $dateObject;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plug-in instance.
   *   This must contain a 'date' key with a string that can be used to build a
   *   DrupalDateTime object with.
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
   * Initialize the DrupalDateTime object for this plug-in instance.
   *
   * @param string $date
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *
   * @throws \InvalidArgumentException
   *   If the DrupalDateTime object reports any errors.
   */
  protected function initializeDateObject(string $date): DrupalDateTime {

    /** @var \Drupal\Core\Datetime\DrupalDateTime */
    $dateObject = DrupalDateTime::createFromFormat(
      self::DATE_FORMAT_STORAGE,
      $date
    );

    if ($dateObject->hasErrors()) {
      throw new \InvalidArgumentException(
        'There were one or more errors in constructing a \Drupal\Core\Datetime\DrupalDateTime object:' .
        "\n" . \implode("\n", $dateObject->getErrors())
      );
    }

    $this->dateObject = $dateObject;

    return $this->dateObject;

  }

  /**
   * {@inheritdoc}
   */
  public function getDateObject(): DrupalDateTime {
    return $this->dateObject;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   *   If $format is not one of the expected values.
   *
   * @todo Should this even throw an exception? What about just passing $format
   *   to DrupalDateTime::format() if it's not one of our keywords instead?
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
