<?php declare(strict_types=1);

namespace App\Controller\View;

use App\Controller\BaseController;
use App\Entity\Statement;
use App\Enum\CacheKeys;
use App\Form\StatementCreateType;
use App\Service\FileUploader;
use App\Service\Pagination\StatementPagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatementController extends BaseController
{
    /**
     * Страница с формой создания заявления
     */
    #[Route(path: '/statements/create', name: 'create_statement')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $statement = new Statement;
        $form = $this->createForm(StatementCreateType::class, $statement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            if ($uploadedFile = $form->get('file')->getData()) {
                $statement->setFile($fileUploader->upload($uploadedFile));
            }

            $statement->setCreator($this->getUser());
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
    #[Route(path: '/statements/list', name: 'statement_list')]
    public function list(
        Request $request,
        StatementPagination $statementPagination
    ): Response
    {
        $page = (int)$request->get('page');
        $statementPagination
            ->init('statement_list', $page)
            ->setUser($this->getUser());

        return $this->render('views/statement/list.html.twig', [
            'statements' => $statementPagination->getResult()['items'],
            'pagination' => $statementPagination->getLinks(),
        ]);
    }

    /**
     * Страница со списком заявлений для администратора
     */
    #[Route(path: '/admin/statements/list', name: 'statement_list_admin')]
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