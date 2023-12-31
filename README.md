# PrestaSonataSavedFiltersBundle

PrestaSonataSavedFiltersBundle will allow your Sonata users to save and share list filters.

## Preview

On each admin, you can save current filters to a dedicated database entry, after naming it.
<img src="doc/images/save-filter-form.png" alt="Save filter form" width="70%">

When you saved a filter, you can reapply it whenever you want on the admin it was created.
<img src="doc/images/saved-filters-dropdown.png" alt="Saved filters dropdown" width="70%">

An admin exists where you can see, share, remove filters created in the application.
<img src="doc/images/saved-filters-admin-list.png" alt="Saved filters admin list" width="70%">

## Installation

Install the bundle with the command: 
```console
$ composer require presta/sonata-saved-filters-bundle
```

Enable the bundle:
```diff
# config/bundles.php
return [
+    Presta\SonataSavedFiltersBundle\PrestaSonataSavedFiltersBundle::class => ['all' => true],
];
```

## Configuration

Import our Javascripts in your project:
```javascript
import '../../public/bundles/prestasonatasavedfilters/scripts/app';
```

> This step is highly dependent on how your public assets are built, imported.
> It's up to you knowing the best way to include it in your project.

Include our action template into the Twig template you configured to be your admin layout:
```twig
{% extends '@SonataAdmin/standard_layout.html.twig' %}

{% block sonata_admin_content_actions_wrappers %}
    {{ parent() }}
    {{ include('@PrestaSonataSavedFilters/saved_filters_action.html.twig') }}
{% endblock %}
```

> See related SonataAdmin [documentation](https://docs.sonata-project.org/projects/SonataAdminBundle/en/4.x/reference/templates/#global-templates)

Configure to doctrine that what entity will be attached to saved filters `config/packages/doctrine.yaml`:

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface, SavedFiltersOwnerInterface
{
}
```

```yaml
# config/packages/doctrine.yaml
doctrine:
  orm:
    resolve_target_entities:
      Presta\SonataSavedFiltersBundle\Entity\SavedFiltersOwnerInterface: 'App\Entity\User'
```

> See related DoctrineBundle [documentation](https://symfony.com/doc/current/doctrine/resolve_target_entity.html)

Add the admin to the menu, where you want it:
```yaml
# config/packages/sonata_admin.yaml
sonata_admin:
    dashboard:
        groups:
          the_group_in_which_you_want_the_admin:
                items:
                    - presta_sonata_saved_filters.saved_filters
```

Finally, update your schema to create the tables required for our entities:
```console
$ bin/console doctrine:schema:update 
```

> Or create a migration if you have `DoctrineMigrationsBundle` installed:
> ```console
> $ bin/console doctrine:migrations:diff
> $ bin/console doctrine:migrations:migrate
> ```

---

*This project is supported by [PrestaConcept](https://www.prestaconcept.net)*

Released under the [MIT License](LICENSE)
