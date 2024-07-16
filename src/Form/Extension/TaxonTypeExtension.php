<?php

namespace Akki\SyliusRegistrationDrawingBundle\Form\Extension;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class TaxonTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registrationDrawing', EntityType::class, [
                'required' => false,
                'class' => RegistrationDrawing::class,
                'label' => 'sylius_registration_drawing.ui.registration_drawing',
                'choice_label' => 'name',
                'choice_value' => 'id'
            ]);
    }

    public static function getExtendedTypes(): iterable
    {
        yield TaxonType::class;
    }
}
