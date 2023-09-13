<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDatelistWidget;
use Drupal\omnipedia_date\Service\DateResolverInterface;
use Drupal\omnipedia_date\Service\TimelineInterface;
use Drupal\omnipedia_date\Value\OmnipediaDateRange;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plug-in implementation of the 'omnipedia_daterange_datelist' widget.
 *
 * This widget vastly simplifies the Drupal core 'daterange_datelist' widget
 * and adapts it to Omnipedia's date system by reducing the start and end
 * dates to one select each, with each select containing a list of the
 * available Omnipedia dates. Addtionally, the start and end dates contain
 * "First date" and "Last date" options, respectively, which always match the
 * first and last defined dates including new dates added by content after
 * this field was saved.
 *
 * @FieldWidget(
 *   id           = "omnipedia_daterange_datelist",
 *   label        = @Translation("Select list (Omnipedia dates)"),
 *   field_types  = {
 *     "daterange",
 *     "omnipedia_daterange"
 *   }
 * )
 */
class OmnipediaDateRangeDatelistWidget extends DateRangeDatelistWidget implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\omnipedia_date\Service\DateResolverInterface $dateResolver
   *   The Omnipedia date resolver service.
   *
   * @param \Drupal\omnipedia_date\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   */
  public function __construct(
    $pluginId, $pluginDefinition, FieldDefinitionInterface $fieldDefinition,
    array $settings, array $thirdPartySettings,
    protected readonly DateResolverInterface  $dateResolver,
    protected readonly TimelineInterface      $timeline,
  ) {

    parent::__construct(
      $pluginId, $pluginDefinition, $fieldDefinition,
      $settings, $thirdPartySettings,
    );

  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId, $pluginDefinition,
  ) {
    return new static(
      $pluginId,
      $pluginDefinition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('omnipedia_date.date_resolver'),
      $container->get('omnipedia.timeline'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items, $delta, array $element,
    array &$form, FormStateInterface $formState,
  ) {
    // Contains all dates that have nodes, in the 'storage' format. This
    // includes unpublished nodes.
    /** @var array */
    $definedDates = $this->timeline->getDefinedDates(true);

    /** @var array */
    $options = [];

    foreach ($definedDates as $dateStorage) {
      // Array keys are the storage format stored in the node fields, while the
      // values are the user-friendly strings presented to the user.
      $options[$dateStorage] = $this->timeline->getDateFormatted(
        $dateStorage, 'short',
      );
    }

    /** @var array */
    $element = parent::formElement($items, $delta, $element, $form, $formState);

   foreach ([
      'value'     => 'first',
      'end_value' => 'last',
    ] as $key => $nullValue) {
      $element[$key]['#type'] = 'select';
      $element[$key]['#options'] = $options;

      if ($items->{$key} !== null) {
        $element[$key]['#default_value'] = $items->{$key};

      } else {
        $element[$key]['#default_value'] = $nullValue;
      }
    }

    $element['value']['#options'] =
      ['first' => $this->t('First date')] + $element['value']['#options'];

    $element['end_value']['#options']['last'] = $this->t('Last date');

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeWidgetBase::validateStartEnd()
   *   Does basically the same thing we do here and nothing else, nor does it
   *   call any parent method, and it assumes '#value' will be an array rather
   *   than a string; because of these reasons, we don't call this to avoid
   *   fatal errors.
   */
  public function validateStartEnd(
    array &$element, FormStateInterface $formState, array &$completeForm,
  ) {

    // Attempt to build a date range object, which will throw an exception if
    // the start date is set to after the end date, thus validating it for us.
    try {

      $dateRange = new OmnipediaDateRange(
        $this->dateResolver->resolve(
          $element['value']['#value'],
        )->getDateObject(),
        $this->dateResolver->resolve(
          $element['end_value']['#value'],
        )->getDateObject(),
      );

    } catch (\Exception $exception) {

      $formState->setError($element, $exception->getMessage());

    }

  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(
    array $values, array $form, FormStateInterface $formState,
  ) {
    // Convert the 'first' and 'last' start and end values to null so that
    // Drupal allows saving them to the date fields.
    foreach ($values as &$item) {
      if ($item['value'] === 'first') {
        $item['value'] = null;
      }
      if ($item['end_value'] === 'last') {
        $item['end_value'] = null;
      }
    }

    /** @var array */
    $values = parent::massageFormValues($values, $form, $formState);

    return $values;
  }

}
