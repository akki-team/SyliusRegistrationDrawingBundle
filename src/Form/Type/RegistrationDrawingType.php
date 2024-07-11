<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Form\Type;

use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class RegistrationDrawingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.name',
            ])
            ->add('encoding', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.encoding',
                'choices' => Constants::ENCODINGS,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('format', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.format',
                'choices' => Constants::OUTPUT_FORMATS,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('delimiter', ChoiceType::class, [
                'required' => true,
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
            ->add('currencyFormat', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.currencyFormat',
                'choices' => Constants::CURRENCY_FORMATS,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('currencyDelimiter', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.currencyDelimiter',
                'choices' => Constants::CURRENCY_DELIMITERS,
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
            ->add('password', TextType::class, [
                'required' => false,
                'label' => 'sylius_registration_drawing.form.registration_drawing.password',
            ])
            ->add('host', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.host',
            ])
            ->add('port', IntegerType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.port',
            ])
            ->add('recipients', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.recipients',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'akki_registration_drawing';
    }
}
