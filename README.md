## Sylius Registration Drawing Bundle

## Description
Registration drawings for Sylius. Manage and export to multiple formats (works with the Sylius marketplace plugin).

## Installation

1. Run `composer require akki/sylius-registration-drawing-bundle`

2. Enable the plugin in bundles.php

```php
<?php
// config/bundles.php

return [
    // ...
    Akki\SyliusRegistrationDrawingBundle\AkkiSyliusRegistrationDrawingPlugin::class => ['all' => true],
];
```

3. Import the plugin configurations

```yml
# config/packages/_sylius.yaml
imports:
    // ...
    - { resource: "@AkkiSyliusRegistrationDrawingPlugin/Resources/config/config.yaml" }
```

4. Add the admin routes

```yml
# config/routes.yaml
akki_sylius_registration_drawing_plugin:
    prefix: /admin
    resource: "@AkkiSyliusRegistrationDrawingPlugin/Resources/config/routing.yaml"
```

5. Add the OrderDrawingRepositoryTrait to the OrderRepository entity

```php
<?php
namespace App\Repository;

use Akki\SyliusRegistrationDrawingBundle\Repository\OrderDrawingRepositoryTrait;
use Akki\SyliusRegistrationDrawingBundle\Repository\OrderRepositoryInterface;


class OrderRepository extends BaseOrderRepository implements OrderRepositoryInterface
{
    use OrderDrawingRepositoryTrait;
```

6. Add the RegistrationDrawingTrait to the Vendor entity

```php
<?php
// Entity/Vendor.php

class Vendor extends BaseVendor implements RegistrationDrawingAwareInterface
{
    use RegistrationDrawingTrait;

    #[ORM\ManyToOne(targetEntity: RegistrationDrawingInterface::class, inversedBy: 'vendors')]
    #[ORM\JoinColumn(name: 'registration_drawing_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected RegistrationDrawingInterface|null $registrationDrawing = null;
```

7. Add the RegistrationDrawingTaxonTrait to the Taxon entity

```php
<?php
// Entity/Taxonomy/Taxon.php

class Taxon extends BaseTaxon implements RegistrationDrawingAwareInterface
{
    use RegistrationDrawingTrait;
    
    #[ORM\ManyToOne(targetEntity: RegistrationDrawingInterface::class, inversedBy: 'titles')]
    #[ORM\JoinColumn(name: 'registration_drawing_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected RegistrationDrawingInterface|null $registrationDrawing = null;

```

8. Add the form field in your admin view to add Vendor registration drawings selection

```html
// templates/bundles/OdiseoSyliusMarketplacePlugin/Admin/Vendor/Tab/_details.html.twig

{{ form_row(form.registrationDrawing) }}
```

9. Add the form field in your admin view to add Taxon registration drawings selection

```html
// templates/bundles/SyliusAdminBundle/Taxon/Tab/_market_place.html.twig

{{ form_row(form.registrationDrawing) }}
```

10. Finish the installation updating the database schema and installing assets

```
php bin/console doctrine:migrations:migrate
php bin/console sylius:theme:assets:install
php bin/console cache:clear
```

11. This bundle provide a command to generate exported files.

```
php bin/console export-drawings:generate
```
