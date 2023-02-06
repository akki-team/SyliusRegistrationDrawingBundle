<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Form\Type;

use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegistrationDrawingType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.name',
            ])

            ->add('format', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.format',
                'choices' => Constants::OUTPUT_FORMATS,
                'expanded' => false,
                'multiple' => false,
            ])

            ->add('delimiter', ChoiceType::class, [
                'required' => false,
                'label' => 'sylius_registration_drawing.form.registration_drawing.delimiter',
                'choices' => Constants::DELIMITERS,
                'expanded' => false,
                'multiple' => false,
            ])

            ->add('periodicity', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.periodicity',
                'choices' => Constants::PERIODICITY,
                'expanded' => false,
                'multiple' => false,
            ])

            ->add('day', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.day',
                'choices' => Constants::DAYS,
                'expanded' => false,
                'multiple' => false,
            ])

            ->add('send_mode', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.send_mode',
                'choices' => Constants::SENDING_METHODS,
                'expanded' => false,
                'multiple' => false,
            ])

            ->add('deposit_address', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.deposit_address',
            ])

            ->add('user', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.user',
            ])

            ->add('host', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.host',
            ])

            ->add('port', IntegerType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.port',
            ])

            ->add('port', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.sender',
            ])

            ->add('port', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.recipients',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'registration_drawing';
    }
}
