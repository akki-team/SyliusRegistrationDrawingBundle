<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Controller;

use Akki\SyliusRegistrationDrawingBundle\Form\Type\DrawingFieldChoiceType;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
}
