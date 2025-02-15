<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\File;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security as OASecurity;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/files')]
class FileController extends BaseController
{
    /**
     * Скачать файл
     */
    #[Route(path: '/{fileId}', name: 'get_file', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Скачивает файл по его id'
    )]
    #[OA\Response(
        response: 404,
        description: 'Файл не найден'
    )]
    #[OA\Parameter(
        name: 'fileId',
        in: 'path',
        description: 'id файла',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Tag(name: 'Файлы')]
    #[OASecurity(name: 'Bearer')]
    public function download(int $fileId, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user Текущий пользователь */
        $user = $this->getUser();

        /** @var File $file */
        $file = $entityManager->getRepository(File::class)->find($fileId);

        // Файл может скачать только пользователь, загрузивший его, или админ
        if (!$file || ($user->getId() !== (int)$file->getCreator()->getId() && !$user->isAdmin())) {
            throw $this->createNotFoundException('Файл не найден');
        }

        // Если файл был удалён на сервере
        if (!is_file(__DIR__  . '/../../../var/uploads/'. $file->getName())) {
            throw $this->createNotFoundException('Файл не найден');
        }

        return $this->file('var/uploads/'. $file->getName());
    }
}