<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Omnipedia\Date;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * The interface for all Omnipedia Date plug-ins.
 */
interface OmnipediaDateInterface {

  /**
   * The date format stored in the database.
   *
   * @see \Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface::DATE_STORAGE_FORMAT
   *   An alias for this Drupal core constant.
   */
  public const DATE_FORMAT_STORAGE = DateTimeItemInterface::DATE_STORAGE_FORMAT;

  /**
   * The date format for output to HTML, usually a <time> element.
   *
   * @see \Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface::DATE_STORAGE_FORMAT
   *   Currently an alias for this Drupal core constant.
   */
  public const DATE_FORMAT_HTML = DateTimeItemInterface::DATE_STORAGE_FORMAT;

  /**
   * The long user-friendly date output format.
   *
   * @see https://www.php.net/manual/en/function.date
   *   Format reference.
   */
  public const DATE_FORMAT_LONG = 'F jS Y';

  /**
   * The short user-friendly date output format.
   *
   * @see https://www.php.net/manual/en/function.date
   *   Format reference.
   */
  public const DATE_FORMAT_SHORT = 'Y/m/d';

  /**
   * Get the DrupalDateTime object wrapped by this plug-in instance.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The DrupalDateTime object this plug-in instance wraps.
   */
  public function getDateObject(): DrupalDateTime;

  /**
   * Get the date formatted as a string.
   *
   * @param string $format
   *   One of:
   *
   *   - 'storage': The date format stored in the database. This is defined by
   *     self::DATE_FORMAT_STORAGE.
   *
   *   - 'html': The date format used when outputting to HTML, usually in a
   *     <time> element. This is defined by
   *     self::DATE_FORMAT_HTML.
   *
   *   - 'long': The long user-friendly date output format. This is defined by
   *     \self::DATE_FORMAT_LONG. This is the
   *     default.
   *
   *   - 'short': The short user-friendly date output format. This is defined by
   *     self::DATE_FORMAT_SHORT.
   *
   * @return string
   *   The formatted date.
   */
  public function format(string $format): string;

}
