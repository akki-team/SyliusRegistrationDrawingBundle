<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Controller;

use Akki\SyliusRegistrationDrawingBundle\Form\Type\DrawingFieldChoiceType;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class DrawingFieldController extends ResourceController
{
    public function renderFieldsAction(Request $request): Response
    {
        $template = $request->attributes->get('template', '@SyliusRegistrationDrawingBundle/Resources/views/Field/fieldChoice.html.twig');

        $formFields = $this->get('form.factory')->create(DrawingFieldChoiceType::class, null, [
            'multiple' => true,
        ]);

        return $this->render($template, ['formFields' => $formFields->createView()]);
    }

    public function renderFieldValueFormsAction(Request $request): Response
    {
        $template = $request->attributes->get('template', '@SyliusRegistrationDrawingBundle/Resources/views/Field/fieldValueForms.html.twig');

        $form = $this->get('form.factory')->create(DrawingFieldChoiceType::class, null, [
            'multiple' => true,
        ]);
        $form->handleRequest($request);

        $fields = $form->getData();
        if (null === $fields) {
            throw new BadRequestHttpException();
        }

        $localeCodes = $this->get('sylius.translation_locale_provider')->getDefinedLocalesCodes();

        $forms = [];
        foreach ($fields as $field) {
            $forms[$field->getName()] = $this->getFieldFormsInAllLocales($field, $localeCodes);
        }

        return $this->render($template, [
            'forms' => $forms,
            'count' => $request->query->get('count'),
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * @param array|string[] $localeCodes
     *
     * @return array|FormView[]
     */
    protected function getFieldFormsInAllLocales($field, array $localeCodes): array
    {
        $fieldForm = $this->get('sylius.form_registry.attribute_type')->get($field->getName(), 'default');

        $forms = [];
        foreach ($localeCodes as $localeCode) {
            $forms[$localeCode] = $this
                ->get('form.factory')
                ->createNamed($this->changeFieldNameToKey($field->getName()), TextType::class)
                ->createView()
            ;
        }

        return $forms;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    protected function changeFieldNameToKey(string $fieldName): string
    {
        return str_replace(' ', '_', strToLower($fieldName));
    }
}
