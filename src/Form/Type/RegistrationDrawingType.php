<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Form\Type;

use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RegistrationDrawingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.name',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('encoding', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.encoding',
                'choices' => Constants::ENCODINGS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('format', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.format',
                'choices' => Constants::OUTPUT_FORMATS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('delimiter', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.delimiter',
                'choices' => Constants::DELIMITERS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('periodicity', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.periodicity',
                'choices' => Constants::PERIODICITY,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('day', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.day',
                'choices' => Constants::DAYS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('currencyFormat', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.currencyFormat',
                'choices' => Constants::CURRENCY_FORMATS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('currencyDelimiter', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.currencyDelimiter',
                'choices' => Constants::CURRENCY_DELIMITERS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('send_mode', ChoiceType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.send_mode',
                'choices' => Constants::SENDING_METHODS,
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('deposit_address', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.deposit_address',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('user', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.user',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('password', TextType::class, [
                'required' => false,
                'label' => 'sylius_registration_drawing.form.registration_drawing.password',
            ])
            ->add('host', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.host',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('port', IntegerType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.port',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
            ->add('recipients', TextType::class, [
                'required' => true,
                'label' => 'sylius_registration_drawing.form.registration_drawing.recipients',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'akki_registration_drawing';
    }
}
