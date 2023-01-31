<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Controller;

use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingField;
use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingFieldAssociation;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Helpers\MbHelper;
use Akki\SyliusRegistrationDrawingBundle\Repository\DrawingFieldAssociationRepository;
use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;

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
    private function prepareDrawingHeaderToCSVExport(RegistrationDrawing $registrationDrawing): array
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

    /**
     * @param RegistrationDrawing $registrationDrawing
     * @param OrderItem $orderItem
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function prepareDrawingfieldsToExport(RegistrationDrawing $registrationDrawing, OrderItem $orderItem): array
    {
        $fieldAssociations = $this->getDrawingRegistrationFields($registrationDrawing);
        $datas = [];

        /** @var DrawingFieldAssociation $fieldAssociation */
        foreach ($fieldAssociations as $fieldAssociation) {
            /** @var DrawingField $field */
            $field = $this->container->get('sylius_registration_drawing.repository.drawing_field')->find($fieldAssociation->getFieldId());
            $listAccessors = $field->getEquivalent();

            $accessors = explode('/', $listAccessors);

            $data = $orderItem;

            foreach ($accessors as $accessor) {
                $data = $this->getAccessor($accessor, $data);

                if ($data === false) {
                    break;
                }
            }

            if ($data !== false) {
                // Formats dateTime
                if (!empty($fieldAssociation->getFormat())) {
                    $data = $data->format($fieldAssociation->getFormat());
                }

                // Selection
                if (!empty($fieldAssociation->getSelection())) {
                    $data = $this->substitute($data, $fieldAssociation->getSelection());
                }

                if ($registrationDrawing->getFormat() === Constants::FIXED_LENGTH_FORMAT) {
                    if (!empty($fieldAssociation->getLength())) {
                        $data = $this->applyPad($data, $fieldAssociation->getLength());
                    }
                }
            } else {
                // Gestion des champs de flux de retour
                if (in_array($field->getName(), Constants::RETURN_FLOW_FIELDS)) {
                    if ($registrationDrawing->getFormat() === Constants::FIXED_LENGTH_FORMAT) {
                        if (!empty($fieldAssociation->getLength())) {
                            $data = $this->applyPad('', $fieldAssociation->getLength());
                        } else {
                            $data = '';
                        }
                    }
                }

                // Gestion des champs avec data à construire
                if ($field->getName() === Constants::DATE_TRANSMISSION_FIELD) {
                    $data = (new \DateTime())->format($fieldAssociation->getFormat());
                }

                if (($field->getName() === Constants::BILLING_COUNTRY_FIELD)) {
                    $data = Intl::getRegionBundle()->getCountryName($orderItem->getOrder()->getBillingAddress()->getCountryCode());
                }

                if (($field->getName() === Constants::SHIPPING_COUNTRY_FIELD)) {
                    $data = Intl::getRegionBundle()->getCountryName($orderItem->getShippingAddress()->getCountryCode());
                }

                if ($registrationDrawing->getFormat() === Constants::FIXED_LENGTH_FORMAT) {
                    if (!empty($fieldAssociation->getLength())) {
                        $data = $this->applyPad($data, $fieldAssociation->getLength());
                    }
                }
            }

            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * @param RegistrationDrawing $registrationDrawing
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getDrawingRegistrationFields(RegistrationDrawing $registrationDrawing): array
    {
        /** @var DrawingFieldAssociationRepository $repository */
        $repository = $this->container->get('sylius_registration_drawing.repository.drawing_field_association');

        if ($registrationDrawing->getFormat() === Constants::CSV_FORMAT) {
            return $repository->getFields($registrationDrawing->getId());
        } else {
            return $repository->getFieldsByPosition($registrationDrawing->getId());
        }
    }

    /**
     * @param $accessor
     * @param $data
     * @return mixed
     */
    private function getAccessor($accessor, $data)
    {
        $getter = 'get' . $accessor;
        if (method_exists($data, $getter)) {
            return call_user_func_array([$data, $getter], []);
        }

        $getter = 'is' . $accessor;
        if (method_exists($data, $getter)) {
            return call_user_func_array([$data, $getter], []);
        }

        return false;
    }

    /**
     * @param RegistrationDrawing $registrationDrawing
     * @param array $orders
     * @param string $filePath
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function exportDrawing(RegistrationDrawing $registrationDrawing, array $orders, string $filePath)
    {
        $headers = $this->prepareDrawingHeaderToCSVExport($registrationDrawing);

        $fields = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            $items = $order->getItems();

            /** @var OrderItem $item */
            foreach ($items as $item) {
                $product = $item->getProduct();

                if ($product->getVendor() === null) {
                    continue;
                }

                $data = $this->prepareDrawingfieldsToExport($product->getVendor()->getRegistrationDrawing(), $item);

                $fields[] = $data;
            }
        }

        if ($registrationDrawing->getFormat() === Constants::CSV_FORMAT) {
            $writer = $this->container->get('Akki\SyliusRegistrationDrawingBundle\Service\ExportService')->exportCSV($headers, $fields, $registrationDrawing->getDelimiter());

            file_put_contents($filePath, $writer->getContent());

            return $writer->getContent();
        } else {
            $text = $this->container->get('Akki\SyliusRegistrationDrawingBundle\Service\ExportService')->exportFixedLength($fields);

            file_put_contents($filePath, $text);

            return $text;
        }
    }

    /**
     * @param $data
     * @param string $selections
     * @return string
     */
    private function substitute($data, string $selections): string
    {
        $returnedData = $data;
        $couples = explode(';', $selections);

        foreach ($couples as $couple) {
            $coupleValues = explode('=>', $couple);
            if (count($coupleValues) > 1) {
                $keyCouple = trim($coupleValues[0]);
                $valueCouple = trim($coupleValues[1]);

                if ($keyCouple === $data) {
                    $returnedData = $valueCouple;
                    break;
                }
            }
        }

        return $returnedData;
    }

    /**
     * @param $value
     * @param $zone
     * @return string
     */
    private function applyPad($value, $zone): string
    {
        $value = trim($value) ;

        $length = mb_strlen($value) ;
        if ($length > $zone){
            $value = mb_substr($value, 0, $zone) ;
        }

        if ($length < $zone){
            $value = MbHelper::mb_str_pad($value, $zone) ;
        }

        return $value ;

    }

}