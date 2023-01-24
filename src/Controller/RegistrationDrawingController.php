<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Controller;

use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingField;
use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingFieldAssociation;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Repository\DrawingFieldAssociationRepositoryInterface;
use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegistrationDrawingController extends ResourceController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function createDrawing(Request $request): Response
    {
        /** @var RepositoryInterface $entityRepository */
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);
        $newResource = $this->newResourceFactory->create($configuration, $this->factory);

        $form = $this->resourceFormFactory->create($configuration, $newResource);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $newResource = $form->getData();
            $this->manager->persist($newResource);
            $this->manager->flush();

            if ($request->request->has('fields')) {
                // Restructuration du tableau des champs avec l'Id et les options renseignées
                $fields = $this->getFormFields($request->request->get('fields'));

                foreach($fields as $key => $field) {
                    $drawingFieldAssociationFactory = $this->get('sylius_registration_drawing.factory.drawing_field_association');

                    /** @var DrawingFieldAssociation $drawingFieldAssociation */
                    $drawingFieldAssociation = $drawingFieldAssociationFactory->createNew();

                    $drawingFieldAssociation->setDrawingId($newResource->getId());
                    $drawingFieldAssociation->setFieldId($key);
                    if (isset($field['order'])) {
                        $drawingFieldAssociation->setOrder((int)$field['order']);
                    }
                    if (isset($field['position'])) {
                        $drawingFieldAssociation->setPosition((int)$field['position']);
                    }
                    if (isset($field['length'])) {
                        $drawingFieldAssociation->setLength((int)$field['length']);
                    }
                    if (isset($field['format'])) {
                        $drawingFieldAssociation->setFormat($field['format']);
                    }
                    if (isset($field['selection'])) {
                        $drawingFieldAssociation->setSelection($field['selection']);
                    }

                    $this->manager->persist($drawingFieldAssociation);
                }

                $this->manager->flush();
            }

            return $this->redirectToRoute('sylius_registration_drawing_admin_registration_drawing_index');
        }

        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                $this->metadata->getName() => $newResource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::CREATE . '.html'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * @param array $formFields
     * @return array
     */
    private function getFormFields(array $formFields): array
    {
        $fields = [];

        foreach ($formFields as $key => $value) {
            if (!is_null($value) && ($value !== "")) {
                $fields[$this->getFieldId($key)][$this->getOptionFromFormFieldKey($key)] = $value;
            }
        }

        return $fields;
    }

    /**
     * @param string $fieldKey
     * @return int
     */
    private function getFieldId(string $fieldKey): int
    {
        // Récupère le dernier nombre qui correspond à l'Id du champ
        preg_match_all('/\d+/', $fieldKey, $numbers);
        return (int)array_pop($numbers)[0];
    }

    /**
     * @param string $fieldKey
     * @return string|bool
     */
    private function getOptionFromFormFieldKey(string $fieldKey)
    {
        $options = Constants::FIELDS_OPTIONS;

        // Découpe la chaine pour cibler l'option
        $key = str_replace('_', ' ', $fieldKey);

        // Récupère l'option du champ (ex: position)
        foreach ($options as $option) {
            if(strpos($key, $option) !== false){
                return $option;
            }
        }

        return false;
    }

    /**
     * @param RegistrationDrawing $registrationDrawing
     * @return array
     */
    public function prepareDrawingHeaderToCSVExport(RegistrationDrawing $registrationDrawing): array
    {
        $header = [];

        $drawingFieldAssociationRepository = $this->container->get('sylius_registration_drawing.repository.drawing_field_association');
        $drawingFieldRepository = $this->container->get('sylius_registration_drawing.repository.drawing_field');

        $fields = $drawingFieldAssociationRepository->getFields($registrationDrawing->getId());

        /** @var DrawingField $field */
        foreach ($fields as $field) {
            $header[] = $drawingFieldRepository->find($field->getFieldId())->getName();
        }

        return $header;
    }

}
