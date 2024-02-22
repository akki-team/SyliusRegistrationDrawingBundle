<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Form\Type;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

final class GeneratedFileType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('drawing', EntityType::class, array(
                'required' => true,
                'label' => 'sylius.admin.ee_generated_file.form.registration_drawing',
                'class' => RegistrationDrawing::class,
                'choice_label' => 'name',
                'choice_value' => 'id'
            ))
            ->add('startDate', DateType::class, [
                'label' => 'sylius.admin.ee_generated_file.form.start_date',
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'label' => 'sylius.admin.ee_generated_file.form.end_date',
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('drop', CheckboxType::class, [
                'label' => 'sylius.admin.ee_generated_file.form.drop',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'akki_generated_files';
    }
}


