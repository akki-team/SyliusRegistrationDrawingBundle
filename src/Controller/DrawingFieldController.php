<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Controller;

use Akki\SyliusRegistrationDrawingBundle\Form\Type\DrawingFieldChoiceType;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Repository\DrawingFieldAssociationRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DrawingFieldController extends ResourceController
{
    public function renderFieldsAction(Request $request, DrawingFieldAssociationRepositoryInterface $drawingFieldAssociationRepository): Response
    {
        $template = $request->attributes->get('template', '@SyliusRegistrationDrawingBundle/Field/fields.html.twig');

        $formFields = $this->createForm(DrawingFieldChoiceType::class, options: [
            'multiple' => true,
        ]);

        $fields = $drawingFieldAssociationRepository->getFields((int)$request->query->get('id'));
        $drawingFieldAssociations = count($fields) > 0 ? $fields : [];

        return $this->render($template, [
            'formFields' => $formFields->createView(),
            'fieldAssociations' => $drawingFieldAssociations,
            'substituableFields' => Constants::SUBSTITUABLE_FIELDS
        ]);
    }
}
