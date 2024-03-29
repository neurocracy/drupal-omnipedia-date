This contains the source files for the "*Omnipedia - Date*" Drupal module, which
provides the simulated date framework and related functionality for
[Omnipedia](https://omnipedia.app/).

⚠️ ***[Why open source? / Spoiler warning](https://omnipedia.app/open-source)***

----

# Description

This contains most of the logic that underpins the simulated date/revision
system for Omnipedia. It includes services, event subscribers, a cache context,
and various plug-ins related to the date system, including a field type, widget,
and formatters.

Note that this does not does not contain the framework to manage the simulated
wiki pages (Drupal nodes) themselves; that can be found in the [`omnipedia_core`
module](https://github.com/neurocracy/drupal-omnipedia-core).

----

# Requirements

* [Drupal 9.5 or 10](https://www.drupal.org/download) ([Drupal 8 is end-of-life](https://www.drupal.org/psa-2021-11-30))

* PHP 8.1

* [Composer](https://getcomposer.org/)

## Drupal dependencies

Follow the Composer installation instructions for these dependencies first:

* The [`omnipedia_core` module](https://github.com/neurocracy/drupal-omnipedia-core).

----

# Installation

## Composer

### Set up

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the `drupal/recommended-project`
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

### Repository

In your root `composer.json`, add the following to the `"repositories"` section:

```json
"drupal/omnipedia_date": {
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-date.git"
}
```

### Installing

Once you've completed all of the above, run `composer require
"drupal/omnipedia_date:^4.0@dev"` in the root of your project to have
Composer install this and its required dependencies for you.

----

# Major breaking changes

The following major version bumps indicate breaking changes:

* 4.x:

  * Requires Drupal 9.5 or [Drupal 10](https://www.drupal.org/project/drupal/releases/10.0.0) with compatibility and deprecation fixes for the latter.

  * Requires PHP 8.1.

  * Increases minimum version of [Hook Event Dispatcher](https://www.drupal.org/project/hook_event_dispatcher) to 3.1, removes deprecated code, and adds support for 4.0 which supports Drupal 10.

* 5.x:

  * This heavily refactors the underlying functionality of the Timeline service into smaller services, plug-ins, and other classes. Most of these don't change backwards compatibility, but a number of parameters and return values have changed slightly.

  * Removed the [`Timeline`](src/Service/Timeline.php) class date constants; these have been moved to [`Plugin\Omnipedia\Date\OmnipediaDateInterface`](src/Plugin/Omnipedia/Date/OmnipediaDateInterface.php).

  * `Timeline::getDateObject()` is now a protected method; this wasn't used in other modules, and all uses of it in this module have been replaced with other, more specific services.

  * `Service\TimelineInterface::setCurrentDate()` has been removed; use [`Service\CurrentDateInterface::set()`](src/Service/CurrentDateInterface.php) instead.

  * `Service\TimelineInterface::findDefinedDates()` has been removed; use [`Service\DefinedDatesInterface::find()`](src/Service/DefinedDatesInterface.php) instead.

  * `Service\TimelineInterface::setDefaultDate()` has been removed; use [`Service\DefaultDateInterface::set()`](src/Service/DefaultDateInterface.php) instead.
