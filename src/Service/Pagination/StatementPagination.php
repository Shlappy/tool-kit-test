<?php

namespace App\Service\Pagination;

use App\Entity\Statement;
use App\Entity\User;
use App\Service\Pagination\PagePaginator;
use App\Service\Pagination\PaginationLinks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

final class StatementPagination
{
    /**
     * @var string Строка с ссылками пагинации
     */
    private string $pagination;

    /**
     * @var ?User Пользователь, заявления которого будут отображены.
     * Если не задано, будут отображены все заявления.
     */
    private ?User $user;

    public function __construct(
        private PagePaginator $paginator,
        private PaginationLinks $paginationLinks,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router
    ) {
        $this->paginator = $paginator;
        $this->paginationLinks = $paginationLinks;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    /**
     * Данные после запроса с пагинацией
     *
     * @param int|null $page
     * @return array
     */
    public function getResult(?int $page = 1): array
    {
        $builder = $this->entityManager->getRepository(Statement::class)
            ->createQueryBuilder('s')
            ->orderBy('s.id', 'desc');

        if ($this->user) {
            $builder->where('s.creator = :creator')
                ->setParameter('creator', $this->user);
        }

        $paginatedStatements = $this->paginator->paginate($builder, $page ?: 1);

        $this->pagination = $this->paginationLinks->generateLinks(
            $paginatedStatements['pages'],
            $paginatedStatements['page'],
            $this->router->generate('statement_list')
        );

        return $paginatedStatements;
    }

    public function getLinks(): string
    {
        return $this->pagination;
    }

    /**
     * Задать пользователя, заявления которого будут отображены
     *
     * @param User|null $user
     * @return string
     */
    public function setUser(?User $user = null): static
    {
        $this->user = $user;

        return $this;
    }
}