## Sylius Registration Drawing Bundle

## Description
Registration drawings for Sylius. Manage and export to multiple formats (works with the Sylius marketplace plugin).

## Installation

1. Run `composer require akki/sylius-registration-drawing-bundle:@dev`

2. Enable the plugin in bundles.php

```php
<?php
// config/bundles.php

return [
    // ...
    Akki\SyliusRegistrationDrawingBundle\RegistrationDrawingBundle::class => ['all' => true],
];
```

3. Import the plugin configurations

```yml
# config/packages/_sylius.yaml
imports:
    // ...
    - { resource: "@RegistrationDrawingBundle/Resources/config/config.yaml" }
```

4. Add the admin routes

```yml
# config/routes.yaml
sylius_registration_drawing:
    prefix: /admin
    resource: "@RegistrationDrawingBundle/Resources/config/routing.yaml"
```

5. Add the RegistrationDrawingTrait to the Vendor entity
```php
<?php
// Entity/Vendor.php

class Vendor extends BaseVendor
{
    use RegistrationDrawingTrait;
```

6. Add the form field in your admin view to add registration drawings selection
```html
// templates/bundles/OdiseoSyliusMarketplacePlugin/Admin/Vendor/Tab/_details.html.twig

{{ form_row(form.registrationDrawing) }}
```

7. Finish the installation updating the database schema and installing assets

```
php bin/console doctrine:migrations:migrate
php bin/console sylius:theme:assets:install
php bin/console cache:clear
```
