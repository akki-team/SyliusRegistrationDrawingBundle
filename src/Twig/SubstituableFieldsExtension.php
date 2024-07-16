<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Twig;

use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SubstituableFieldsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('akki_registration_drawing_substituable_fields', [$this, 'getSubstituableFields']),
        ];
    }

    public function getSubstituableFields(): array
    {
        return Constants::SUBSTITUABLE_FIELDS;
    }

}
