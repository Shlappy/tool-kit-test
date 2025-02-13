<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
        #[Autowire(expression: 'service("security.helper").getUser()')]
        private User $user
    ) {
    }

    /**
     * Сохраняет файл в директорию загрузок и создаёт новую сущность
     */
    public function upload(UploadedFile $uploadedFile): File
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move($this->getTargetDirectory(), $fileName);

        $file = (new File)
            ->setCreator($this->user)
            ->setName($fileName);

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        return $file;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}