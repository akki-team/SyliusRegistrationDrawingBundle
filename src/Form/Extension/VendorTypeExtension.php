<?php

namespace Akki\SyliusRegistrationDrawingBundle\Form\Extension;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Odiseo\SyliusVendorPlugin\Form\Type\VendorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class VendorTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registrationDrawing', EntityType::class, [
                'required' => false,
                'class' => RegistrationDrawing::class,
                'label' => 'sylius_registration_drawing.ui.registration_drawings',
                'choice_label' => 'name',
                'choice_value' => 'id'
            ]);
    }

    /**
     */
    public function getExtendedTypes(): iterable
    {
        return [VendorType::class];
    }
}
