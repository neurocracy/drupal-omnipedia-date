<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDatelistWidget;
use Drupal\omnipedia_date\Service\TimelineInterface;
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
   * @param \Drupal\omnipedia_date\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   */
  public function __construct(
    $pluginId, $pluginDefinition, FieldDefinitionInterface $fieldDefinition,
    array $settings, array $thirdPartySettings,
    protected readonly TimelineInterface $timeline,
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
   */
  public function validateStartEnd(
    array &$element, FormStateInterface $formState, array &$completeForm,
  ) {
    // Only call the parent validate if neither the start or end dates are set
    // to the first and last dates, respectively, as
    // DateRangeWidgetBase::validateStartEnd() expects their values to contain
    // arrays with DrupalDateTime objects, which they don't if set to the first
    // or last date values. If one of these values is set and the other is set
    // to a specific date, it will always be valid by definition.
    if (
      $element['value']['#value'] !== 'first' &&
      $element['end_value']['#value'] !== 'last'
    ) {
      parent::validateStartEnd($element, $formState, $completeForm);
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
