<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface;
use Drupal\omnipedia_date\Service\TimelineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the NonOverlappingEntityDateRange constraint.
 */
class NonOverlappingEntityDateRangeValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type plug-in manager.
   *
   * @param \Drupal\omnipedia_date\Service\TimelineInterface $timeline
   *   The Omnipedia timeline service.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly TimelineInterface $timeline,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('omnipedia.timeline'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface */
    $entity = $value->getEntity();

    /** @var \Drupal\Core\Entity\EntityStorageInterface The entity storage for this entity type. */
    $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());

    /** @var \Drupal\Core\Entity\Query\QueryInterface */
    $query = ($storage->getQuery())
      // Exclude the entity being validated, as it'll always overlap with
      // itself.
      ->condition('id', $entity->id(), '<>')
      // Tag the query so it can be altered if needed.
      ->addTag('non_overlapping_entity_date_range_validate')
      // This is the entity being validated. Retrieve with
      // $query->getMetaData('entity_validate') if alterting this query.
      ->addMetaData('entity_validate', $entity)
      ->accessCheck(true);

    /** @var string[] Zero or more entity IDs, keyed by their most recent revision ID. */
    $queryResult = $query->execute();

    /** @var \Drupal\omnipedia_date\Entity\EntityWithDateRangeInterface[] */
    $otherEntities = $storage->loadMultiple($queryResult);

    // If there are no other attached data with the same target, the date
    // range is considered valid.
    if (empty($otherEntities)) {
      return;
    }

    foreach ($value as $delta => $item) {

      /** @var string  */
      $startDate = $entity->getStartDate();

      /** @var string  */
      $endDate = $entity->getEndDate();

      foreach ($otherEntities as $otherEntityId => $otherEntity) {

        /** @var string  */
        $otherStartDate = $otherEntity->getStartDate();

        /** @var string  */
        $otherEndDate = $otherEntity->getEndDate();

        // This will attempt to build two date range objects and one or both may
        // throw an exception which needs to be caught and output as a violation
        // rather than causing a fatal error.
        try {

          $overlap = $this->timeline->doDateRangesOverlap(
            $startDate, $endDate, $otherStartDate, $otherEndDate, true,
          );

        } catch (\Exception $exception) {

          $this->context->addViolation($exception->getMessage());

          continue;

        }

        if ($overlap === false) {
          continue;
        }

        $this->context->addViolation(
          $constraint->message, [
            '%entityLabel'  => $otherEntity->label(),
            ':entityUrl'    => $otherEntity->toUrl()->toString(),
            '%startDate'    => $this->timeline->getDateFormatted(
              $otherStartDate, 'short'
            ),
            '%endDate'      => $this->timeline->getDateFormatted(
              $otherEndDate, 'short'
            ),
          ]
        );

      }

    }

  }

}
