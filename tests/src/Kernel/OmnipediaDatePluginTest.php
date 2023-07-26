<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Kernel;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDate;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;

/**
 * Tests for the Omnipedia date plug-in.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class OmnipediaDatePluginTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['omnipedia_core', 'omnipedia_date'];

  /**
   * Attempt to create an Omnipedia date plug-in with the provided date string.
   *
   * @param string $date
   *
   * @return \Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface
   */
  protected function createDatePlugin(string $date): OmnipediaDateInterface {

    return new OmnipediaDate([
      'id'    => 'date:' . $date,
      'date'  => $date,
    ], $date, []);

  }

  /**
   * Test that the date object is correctly initialized.
   */
  public function testValidDateObjectInitialization(): void {

    $plugin = $this->createDatePlugin('2049-10-01');

    $this->assertIsObject($plugin);

    $this->assertInstanceOf(
      DateTimePlus::class,
      $plugin->getDateObject(),
    );

  }

  /**
   * Test that the date object is correctly initialized.
   */
  public function testInvalidDateObjectInitialization(): void {

    $this->expectException(\InvalidArgumentException::class);

    $plugin = $this->createDatePlugin('nonsense-value');

  }

  /**
   * Test that valid format keywords result in the expected formatted date.
   *
   * @todo Should we also test for the possiblity that the formats are not
   *   correctly defined on OmnipediaDateInterface and copy them here to test
   *   against or is that overkill?
   */
  public function testValidFormatKeywords(): void {

    foreach ([
      '2049-09-27',
      '2049-09-28',
      '2049-09-30',
      '2049-10-03',
      '2049-10-07',
      '2049-10-10',
    ] as $date) {

      $plugin = $this->createDatePlugin($date);

      foreach ([
        'storage' => OmnipediaDateInterface::DATE_FORMAT_STORAGE,
        'html'    => OmnipediaDateInterface::DATE_FORMAT_HTML,
        'long'    => OmnipediaDateInterface::DATE_FORMAT_LONG,
        'short'   => OmnipediaDateInterface::DATE_FORMAT_SHORT,
      ] as $keyword => $format) {

        $expected = DateTimePlus::createFromFormat(
          OmnipediaDateInterface::DATE_FORMAT_STORAGE,
          $date,
          null,
        )->format($format);

        $this->assertEquals($expected, $plugin->format($keyword));

      }

    }

  }

  /**
   * Test that invalid format keywords result in an exception.
   */
  public function testInvalidFormatKeywords(): void {

    $plugin = $this->createDatePlugin('2049-10-01');

    $this->expectException(\InvalidArgumentException::class);

    $plugin->format('baguette');

    $this->expectException(\InvalidArgumentException::class);

    $plugin->format('baby-shark-do-do-do-do');

    for ($i=0; $i <= 10; $i++) {

      $this->expectException(\InvalidArgumentException::class);

      $plugin->format($this->randomMachineName());

    }

  }

}
