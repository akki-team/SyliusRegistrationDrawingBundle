<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Event;

use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingFieldAssociation;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use Akki\SyliusRegistrationDrawingBundle\Repository\DrawingFieldAssociationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class SetDrawingFieldAssociationEventListener
{
    public function __construct(
        private RequestStack                               $requestStack,
        private DrawingFieldAssociationRepositoryInterface $drawingFieldAssociationRepository,
        private FactoryInterface                           $drawingFieldAssociationFactory,
        private EntityManagerInterface                     $entityManager,
    )
    {
    }

    public function __invoke(ResourceControllerEvent $event): void
    {
        $registrationDrawing = $event->getSubject();

        if (false === $registrationDrawing instanceof RegistrationDrawingInterface) {
            return;
        }

        $request = $this->requestStack->getMainRequest();

        $fields = $request->get('fields', []);

        foreach ($fields as $fieldId => $field) {

            /** @var DrawingFieldAssociation $drawingFieldAssociation */
            $drawingFieldAssociation = $this->drawingFieldAssociationRepository->findOneBy([
                'fieldId' => $fieldId,
                'drawingId' => $registrationDrawing->getId(),
            ]);

            if (null === $drawingFieldAssociation) {
                $drawingFieldAssociation = $this->drawingFieldAssociationFactory->createNew();
                $drawingFieldAssociation->setFieldId($fieldId);
                $drawingFieldAssociation->setDrawingId($registrationDrawing->getId());

                $this->entityManager->persist($drawingFieldAssociation);
            }

            if (true === array_key_exists('name', $field)) {
                $drawingFieldAssociation->setName($field['name']);
            }

            if (true === array_key_exists('order', $field)) {
                $drawingFieldAssociation->setOrder((int)$field['order']);
            }

            if (true === array_key_exists('position', $field)) {
                $drawingFieldAssociation->setPosition((int)$field['position']);
            }

            if (true === array_key_exists('length', $field)) {
                $drawingFieldAssociation->setLength((int)$field['length']);
            }

            if (true === array_key_exists('format', $field)) {
                $drawingFieldAssociation->setFormat($field['format']);
            }

            if (true === array_key_exists('selection', $field)) {
                $drawingFieldAssociation->setSelection($field['selection']);
            }
        }

        $this->clearUnusedFields($registrationDrawing);

        $this->entityManager->flush();
    }

    private function clearUnusedFields(RegistrationDrawingInterface $registrationDrawing): void
    {
        $drawingFieldAssociations = $this->drawingFieldAssociationRepository->getFields($registrationDrawing->getId());

        $request = $this->requestStack->getMainRequest();

        $fields = $request->get('fields', []);

        if (true === empty($fields)) {
            foreach ($drawingFieldAssociations as $drawingFieldAssociation) {
                $this->entityManager->remove($drawingFieldAssociation);
            }
        }

        foreach ($drawingFieldAssociations as $drawingFieldAssociation) {
            if (false === array_key_exists($drawingFieldAssociation->getFieldId(), $fields)) {
                $this->entityManager->remove($drawingFieldAssociation);
            }
        }
    }
}
