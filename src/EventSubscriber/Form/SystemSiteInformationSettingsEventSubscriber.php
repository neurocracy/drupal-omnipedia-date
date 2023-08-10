<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\EventSubscriber\Form;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\omnipedia_date\Service\DateCollectionInterface;
use Drupal\omnipedia_date\Service\DefaultDateInterface;
use Drupal\omnipedia_date\Service\DefinedDatesInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter the 'system_site_information_settings' form to add default date field.
 */
class SystemSiteInformationSettingsEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_date\Service\DateCollectionInterface $dateCollection
   *   The Omnipedia date collection service.
   *
   * @param \Drupal\omnipedia_date\Service\DefaultDateInterface $defaultDate
   *   The Omnipedia default date service.
   *
   * @param \Drupal\omnipedia_date\Service\DefinedDatesInterface $definedDates
   *   The Omnipedia defined dates service.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    protected readonly DateCollectionInterface  $dateCollection,
    protected readonly DefaultDateInterface     $defaultDate,
    protected readonly DefinedDatesInterface    $definedDates,
    protected $stringTranslation,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::PREFIX . 'form_' .
        'system_site_information_settings' .
      '.alter' => 'onFormAlter',
    ];
  }

  /**
   * Alter the 'system_site_information_settings' form.
   *
   * This adds a default date select element under the front page section.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event object.
   */
  public function onFormAlter(FormIdAlterEvent $event): void {

    /** @var array */
    $form = &$event->getForm();

    $definedDates = $this->definedDates->get(true);

    /** @var string[] Defined dates as options for the select element. */
    $dateOptions = [];

    foreach ($definedDates as $date) {
      $dateOptions[$date] = $this->dateCollection->get($date)->format('short');
    }

    /** @var int|bool Form array offset of 'front_page' key, if found, or false. */
    $offset = \array_search('front_page', \array_keys($form));

    /** @var array The Date form container element. */
    $dateContainer = [
      '#type'   => 'details',
      '#open'   => true,
      '#title'  => $this->t('Date'),
    ];

    // If we found the offset, place our Date form container just after the
    // 'front_page' key. We could use #weight here, but that's its own mess
    // because most or all of the existing elements don't have a #weight so we
    // would have to add them ourselves here. Rather than doing that, we can
    // splice in our key just after the 'front_page' key with this convoluted
    // method because \array_splice() doesn't preserve keys.
    //
    // @see https://stackoverflow.com/questions/1783089/array-splice-for-associative-arrays/1783125#1783125
    if (\is_int($offset)) {

      // We want to insert our container after the 'front_page' key, not before.
      $offset++;

      $form = \array_slice($form, 0, $offset, true) + [
        'date' => $dateContainer,
      ] + \array_slice($form, $offset, null, true);

    // If we didn't find the offset, just append it to the form.
    } else {
      $form['date'] = $dateContainer;
    }

    $form['date']['default_date'] = [
      '#type'           => 'select',
      '#default_value'  => $this->defaultDate->get(),
      '#options'        => $dateOptions,
      '#required'       => true,
      '#title'          => $this->t('Default date'),
      '#description'    => $this->t('The default date a user will start on if they don\'t already have a current date set.<br><em>Note that this is ignored if they arrive on the site on a path that specifies a date, such as on wiki pages.</em>'),
    ];

    // Prepend our submit handler to the #submit array so it's triggered before
    // the default one.
    \array_unshift($form['#submit'], [$this, 'submitDefaultDate']);

  }

  /**
   * Submit callback to update the default date based on input.
   *
   * @param array &$form
   *   The whole form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   */
  public function submitDefaultDate(
    array $form, FormStateInterface $formState,
  ): void {

    $this->defaultDate->set($formState->getValue('default_date'));

  }

}
