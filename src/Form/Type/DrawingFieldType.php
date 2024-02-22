<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class DrawingFieldType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.drawing_field.name',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'akki_drawing_field';
    }
}
