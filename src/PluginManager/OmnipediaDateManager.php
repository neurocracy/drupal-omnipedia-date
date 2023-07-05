<?php

declare(strict_types=1);

namespace Drupal\omnipedia_date\PluginManager;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\omnipedia_date\Annotation\OmnipediaDate as OmnipediaDateAnnotation;
use Drupal\omnipedia_date\Plugin\Omnipedia\Date\OmnipediaDateInterface;
use Drupal\omnipedia_date\PluginManager\OmnipediaDateManagerInterface;

/**
 * The Omnipedia Date plug-in manager.
 */
class OmnipediaDateManager extends DefaultPluginManager implements OmnipediaDateManagerInterface {

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plug-in
   *   implementations.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend instance to use.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler to invoke the alter hook with.
   *
   * @see \Drupal\plugin_type_example\SandwichPluginManager
   *   This method is based heavily on the sandwich manager from the
   *   'examples' module.
   */
  public function __construct(
    \Traversable            $namespaces,
    CacheBackendInterface   $cacheBackend,
    ModuleHandlerInterface  $moduleHandler
  ) {

    parent::__construct(

      // This tells the plug-in manager to look for OmnipediaAttachedData
      // plug-ins in the 'src/Plugin/Omnipedia/Date' subdirectory of any
      // enabled modules. This also serves to define the PSR-4 subnamespace in
      // which OmnipediaAttachedData plug-ins will live.
      'Plugin/Omnipedia/Date',

      $namespaces,

      $moduleHandler,

      // The name of the interface that plug-ins should adhere to. Drupal will
      // enforce this as a requirement. If a plug-in does not implement this
      // interface, Drupal will throw an error.
      OmnipediaDateInterface::class,

      // The name of the annotation class that contains the plug-in definition.
      OmnipediaDateAnnotation::class

    );

    // This allows the plug-in definitions to be altered by an alter hook. The
    // parameter defines the name of the hook:
    //
    // hook_omnipedia_date_info_alter()
    $this->alterInfo('omnipedia_date_info');

    // This sets the caching method for our plug-in definitions. Plug-in
    // definitions are discovered by examining the directory defined above, for
    // any classes with a OmnipediaDateAnnotation::class. The annotations are
    // read, and then the resulting data is cached using the provided cache
    // backend.
    $this->setCacheBackend($cacheBackend, 'omnipedia_date_info');

  }

}
