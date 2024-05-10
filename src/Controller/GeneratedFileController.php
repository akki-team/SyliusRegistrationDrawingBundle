<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Controller;

use Akki\SyliusRegistrationDrawingBundle\Entity\GeneratedFile;
use Akki\SyliusRegistrationDrawingBundle\Form\Type\GeneratedFileType;
use Akki\SyliusRegistrationDrawingBundle\Service\ExportServiceInterface;
use Akki\SyliusRegistrationDrawingBundle\Service\GeneratedFileServiceInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GeneratedFileController extends ResourceController
{
    public function downloadAction(int $generatedFileId, GeneratedFileServiceInterface $generatedFileService): Response
    {
        return $generatedFileService->readStream($generatedFileId);
    }

    public function replaySendingGeneratedFileAction(
        Request                       $request,
        GeneratedFileServiceInterface $generatedFileService,
        ExportServiceInterface        $exportService,
    ): RedirectResponse
    {
        $name = $request->query->get('name');
        $generatedFile = $generatedFileService->findElement($name);

        $exportService->sendSalesReportToVendor(
            $generatedFile->getRegistrationDrawing(),
            $generatedFile->getPath()
        );

        $this->addFlash('success', 'Dépot serveur rejoué.');

        return $this->redirectToRoute('akki_admin_generated_file_index');
    }

    public function generateFileAction(Request $request, ExportServiceInterface $exportService, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(GeneratedFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $request->get('akki_generated_files');
            $drop = true === array_key_exists('drop', $data) && $data['drop'];

            /** @var GeneratedFile $generatedFile */
            $generatedFile = $form->getData();

            try {
                $exportService->exportDrawing(
                    $generatedFile->getRegistrationDrawing(),
                    $generatedFile->getStartDate(),
                    $generatedFile->getEndDate()->setTime(23, 59, 59),
                    $drop
                );
                $this->addFlash('success', $translator->trans('sylius.admin.ee_generated_file.success_file_generated'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }


            return $this->redirectToRoute('akki_admin_generated_file_index');
        }

        if ($form->isSubmitted() && false === $form->isValid()) {

            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }

            return $this->redirectToRoute('akki_admin_generated_file_index');
        }

        return $this->render('@AkkiSyliusRegistrationDrawingPlugin/GeneratedFiles/Grid/Generate/_generateFile.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
