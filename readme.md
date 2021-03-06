This contains the source files for the "*Omnipedia - Date*" Drupal module, which
provides the simulated date framework and related functionality for
[Omnipedia](https://omnipedia.app/).

⚠️⚠️⚠️ ***Here be potential spoilers. Proceed at your own risk.*** ⚠️⚠️⚠️

----

# Why open source?

We're dismayed by how much knowledge and technology is kept under lock and key
in the videogame industry, with years of work often never seeing the light of
day when projects are cancelled. We've gotten to where we are by building upon
the work of countless others, and we want to keep that going. We hope that some
part of this codebase is useful or will inspire someone out there.

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

# Planned improvements

* [Refactor out Timeline date object building, comparison, and formatting](https://github.com/neurocracy/drupal-omnipedia-date/issues/1).

----

# Requirements

* [Drupal 9](https://www.drupal.org/download) ([Drupal 8 is end-of-life](https://www.drupal.org/psa-2021-11-30))

* PHP 8

* [Composer](https://getcomposer.org/)

----

# Installation

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the ```drupal\recommended-project```
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

Then, in your root ```composer.json```, add the following to the
```"repositories"``` section:

```
{
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-date.git"
}
```

Then, in your project's root, run ```composer require
"drupal/omnipedia_date:3.x-dev@dev"``` to have Composer install the module
and its required dependencies for you.
