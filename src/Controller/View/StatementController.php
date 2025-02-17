<?php declare(strict_types=1);

namespace App\Controller\View;

use App\Controller\BaseController;
use App\Entity\Statement;
use App\Entity\User;
use App\Enum\CacheKeys;
use App\Form\Type\StatementCreateType;
use App\Service\FileUploader;
use App\Service\Pagination\StatementPagination;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security as OASecurity;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatementController extends BaseController
{
    /**
     * Страница с формой создания заявления.
     * GET  - страница с формой
     * POST - отправка данных для создания заявления
     */
    #[Route(path: '/statements/create', name: 'create_statement', methods: ['GET', 'POST'])]
    #[OA\Response(
        response: 200,
        description: 'Страница с формой создания заявления'
    )]
    #[OA\Response(
        response: 422,
        description: 'Неверные данные'
    )]
    #[OA\Tag(name: 'Заявление')]
    #[OASecurity(name: 'Bearer')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $statement = new Statement;
        $form = $this->createForm(StatementCreateType::class, $statement, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($uploadedFile = $form->get('file')->getData()) {
                $statement->setFile($fileUploader->upload($uploadedFile));
            }

            /** @var User $user */
            $user = $this->getUser();
            $statement->setCreator($user);
            $entityManager->persist($statement);
            $entityManager->flush();

            return $this->redirectToRoute('statement_list');
        }

        return $this->render('views/statement/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Страница со списком заявлений
     */
    #[Route(path: '/statements/list', name: 'statement_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Страница со списком заявлений'
    )]
    #[OA\Tag(name: 'Заявление')]
    #[OASecurity(name: 'Bearer')]
    public function list(
        Request $request,
        StatementPagination $statementPagination
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $page = (int)$request->get('page');
        $statementPagination
            ->init('statement_list', $page)
            ->setUser($user);

        return $this->render('views/statement/list.html.twig', [
            'statements' => $statementPagination->getResult()['items'],
            'pagination' => $statementPagination->getLinks(),
        ]);
    }

    /**
     * Страница со списком заявлений для администратора
     */
    #[Route(path: '/admin/statements/list', name: 'statement_list_admin', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Страница со списком заявлений для администратора'
    )]
    #[OA\Tag(name: 'Заявление')]
    #[OASecurity(name: 'Bearer')]
    public function listForAdmin(
        Request $request,
        StatementPagination $statementPagination
    ): Response
    {
        $page = (int)$request->get('page');
        $statementPagination
            ->init('statement_list_admin', $page)
            ->setCache(CacheKeys::ADMIN_STATEMENT_LIST->value, 5);

        return $this->render('views/statement/list-admin.html.twig', [
            'statements' => $statementPagination->getResult()['items'],
            'pagination' => $statementPagination->getLinks(),
        ]);
    }
}