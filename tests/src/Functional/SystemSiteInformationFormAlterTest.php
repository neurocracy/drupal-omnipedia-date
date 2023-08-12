<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_date\Functional;

use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Tests for the 'system_site_information_settings' form alter.
 *
 * @group omnipedia
 *
 * @group omnipedia_date
 */
class SystemSiteInformationFormAlterTest extends BrowserTestBase {

  /**
   * The Drupal state key where we store the list of dates defined by content.
   */
  protected const DEFINED_DATES_STATE_KEY = 'omnipedia.defined_dates';

  /**
   * The Omnipedia default date service.
   *
   * @var \Drupal\omnipedia_date\Service\DefaultDateInterface
   */
  protected readonly DefaultDateInterface $defaultDate;

  /**
   * The user entity created for this test.
   *
   * @var \Drupal\user\UserInterface
   */
  protected readonly UserInterface $testUser;

  /**
   * Defined dates to generate for the test, in storage format.
   *
   * @var string[]
   */
  protected array $definedDatesData = [
    '2049-09-28',
    '2049-09-29',
    '2049-09-30',
    '2049-10-01',
    '2049-10-02',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['omnipedia_date'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    // Set the defined dates to state so that the Omnipedia defined dates
    // service finds those and doesn't attempt to build them from the wiki node
    // tracker, which would return no values as we haven't created any wiki
    // nodes for this test.
    $this->container->get('state')->set(self::DEFINED_DATES_STATE_KEY, [
      'all'       => $this->definedDatesData,
      'published' => $this->definedDatesData,
    ]);

    $this->defaultDate = $this->container->get('omnipedia_date.default_date');

    // Set the initial default date to the last one so that the first iteration
    // of looping through the defined dates actually changes the value. This
    // isn't strictly necessary, but it would make that first iteration
    // pointless if we didn't do this.
    $this->defaultDate->set(\end($this->definedDatesData));

    $this->testUser = $this->drupalCreateUser([
      'access administration pages',
      'administer site configuration',
    ]);

    $this->drupalCreateContentType(['type' => 'page']);

    /** @var \Drupal\node\NodeInterface A node to set as the front page to avoid errors. */
    $node = $this->drupalCreateNode([
      'type'  => 'page',
    ]);

    /** @var \Drupal\Core\Config\Config */
    $config = $this->container->get('config.factory')->getEditable(
      'system.site',
    );

    $config->set('page.front', $node->toUrl()->toString())->save();

  }

  /**
   * Test manipulating the default date values on the site information form.
   */
  public function testSiteInformationForm(): void {

    $this->drupalLogin($this->testUser);

    $this->drupalGet('admin/config/system/site-information');

    /** @var string The name of our <select> element. */
    $selectName = 'default_date';

    $this->assertSession()->selectExists($selectName);

    // Assert that all of the defined dates exist as options in the <select>.
    foreach ($this->definedDatesData as $date) {

      $this->assertSession()->optionExists($selectName, $date);

    }

    // Then change the default date by submitting the form and assert that the
    // default date has been correctly updated.
    foreach ($this->definedDatesData as $date) {

      $this->submitForm([
        $selectName => $date,
      ], 'Save configuration');

      $this->assertEquals($date, $this->defaultDate->get());

    }

  }

}
