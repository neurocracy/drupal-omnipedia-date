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

    $form['front_page']['default_date'] = [
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
