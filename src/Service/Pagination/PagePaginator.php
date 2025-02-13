<?php

namespace App\Service\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PagePaginator
{
    /**
     * @var int Кол-во записей на одну страницу по умолчанию
     */
    protected int $defaultLimit = 10;

    /**
     * @var int Максимальное кол-во записей на одну страницу
     */
    protected int $maxLimit = 100;

    /**
     * Пагинация для моделей
     *
     * @param QueryBuilder $queryBuilder
     * @param int $page
     * @param int|null $limit
     * @return array
     */
    public function paginate(QueryBuilder $queryBuilder, int $page = 1, ?int $limit = null): array
    {
        $limit = $limit ?? $this->defaultLimit;
        $limit = min($limit, $this->maxLimit);
        $offset = ($page - 1) * $limit;

        // Установить пагинацию в запросе
        $queryBuilder->setMaxResults($limit)
            ->setFirstResult($offset);

        $paginator = new Paginator($queryBuilder);
        $totalResults = count($paginator);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $totalResults,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalResults / $limit),
        ];
    }
}