<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\GeneratedFileInterface;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Gaufrette\FilesystemInterface;
use Odiseo\SyliusMarketplacePlugin\Entity\VendorInterface;
use ReflectionClass;
use SplFileInfo;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Webmozart\Assert\Assert;

final readonly class GeneratedFileService implements GeneratedFileServiceInterface
{
    public function __construct(
        private RepositoryInterface    $generatedFileRepository,
        private RepositoryInterface    $registrationDrawingRepository,
        private FactoryInterface       $generatedFileFactory,
        private EntityManagerInterface $entityManager,
        private FilesystemInterface    $filesystem,
    )
    {
    }

    public function addFile(VendorInterface|null $vendor, string $name, string $path, DateTimeInterface $startDate, DateTimeInterface $endDate, ?int $totalLines = null, ?int $totalCancellations = null, ?RegistrationDrawingInterface $registrationDrawing = null): void
    {
        $generatedFile = $this->generatedFileRepository->findOneBy(['name' => $name]);

        if ($generatedFile === null) {

            /** @var GeneratedFileInterface $generatedFile */
            $generatedFile = $this->generatedFileFactory->createNew();

            $generatedFile->setVendor($vendor);
            $generatedFile->setName($name);
            $generatedFile->setPath($path);
            $generatedFile->setStartDate($startDate);
            $generatedFile->setEndDate($endDate);
            $generatedFile->setTotalLines($totalLines);
            $generatedFile->setTotalCancellations($totalCancellations);
            $generatedFile->setRegistrationDrawing($registrationDrawing);

            $this->entityManager->persist($generatedFile);
            $this->entityManager->flush();
        }
    }

    public function readStream(int $id): BinaryFileResponse
    {
        /** @var  GeneratedFileInterface|null $generatedFile */
        $generatedFile = $this->generatedFileRepository->find($id);

        Assert::notNull($generatedFile);

        $fileStream = sprintf('gaufrette://fs_exportsediteur/%s', $generatedFile->getName());

        $response = new BinaryFileResponse($fileStream);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $generatedFile->getName(),
        );

        return $response;
    }

    public function remove(string $path): bool
    {
        if ($this->filesystem->has($path)) {
            return $this->filesystem->delete($path);
        }

        return false;
    }

    public function fsFilesList(): array
    {
        return $this->filesystem->listKeys();
    }

    public function syncFiles(): void
    {
        $list = $this->fsFilesList();

        $adapter = $this->filesystem->getAdapter();

        $reflection = new ReflectionClass($adapter);
        $directory = $reflection->getProperty('directory');
        $directory->setAccessible(true);
        $path = $directory->getValue($adapter);

        foreach ($list["keys"] as $file) {
            $splFile = new SplFileInfo($file);
            $fileWithoutExtension = $splFile->getBasename('.' . $splFile->getExtension());
            $editorCodeExploded = explode('_', $fileWithoutExtension);

            if (count($editorCodeExploded) < 3) {
                continue;
            }

            $drawingName = $editorCodeExploded[0];
            $stringStartDate = $editorCodeExploded[1];
            $stringEndDate = $editorCodeExploded[2];

            $startDate = DateTime::createFromFormat('Y-m-d', $stringStartDate) ?: DateTime::createFromFormat('Ymd', $stringStartDate);
            $endDate = DateTime::createFromFormat('Y-m-d', $stringEndDate) ?: DateTime::createFromFormat('Ymd', $stringEndDate);

            if (!$startDate || !$endDate) {
                continue;
            }

            $drawing = $this->registrationDrawingRepository->findOneBy(['name' => $drawingName]);

            if (false === $drawing instanceof RegistrationDrawingInterface) {
                continue;
            }

            if (true === $drawing->getVendors()->isEmpty()) {
                continue;
            }

            $this->addFile($drawing->getVendors()->toArray()[0], $file, $path . '/' . $file, $startDate, $endDate, registrationDrawing: $drawing);

        }
    }

    public function findElement(string $name): GeneratedFileInterface|null
    {
        return $this->generatedFileRepository->findOneBy(['name' => $name]);
    }

}
