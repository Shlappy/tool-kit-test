<?php

namespace App\Service\Pagination;

use App\Entity\Statement;
use App\Entity\User;
use App\Service\Cache\AppCacheInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

final class StatementPagination
{
    /**
     * @var bool Нужно ли кешировать полученный результат
     */
    private bool $shouldCache = false;

    /**
     * @var string Путь, для которого создаются ссылки
     */
    private string $route;

    /**
     * @var int Текущая страница
     */
    private int $page = 1;

    /**
     * @var string|null Ключ для кеширования
     */
    private ?string $cacheKey = null;

    /**
     * @var int|null Время кеширования
     */
    private ?int $cacheTimeInMin = null;

    /**
     * @var array|null Результат пагинации
     */
    private ?array $paginatedResult = null;

    /**
     * @var User|null Пользователь, заявления которого будут отображены.
     * Если не задано, будут отображены все заявления.
     */
    private ?User $user = null;

    public function __construct(
        private PagePaginator $paginator,
        private PaginationLinks $paginationLinks,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private AppCacheInterface $cache
    ) {
    }

    /**
     * Данные запроса с пагинацией с кешированием результата запроса
     *
     * @return array|null
     */
    public function getResult(): ?array
    {
        if ($this->shouldCache) {
            $this->paginatedResult = $this->getFromCache();
        } else {
            $this->paginatedResult = $this->fetchData();
        }

        return $this->paginatedResult;
    }

    /**
     * Данные запроса с пагинацией напрямую из БД, без кеширования результата запроса
     *
     * @return array|null
     */
    private function fetchData(): ?array
    {
        $builder = $this->entityManager
            ->getRepository(Statement::class)
            ->createQueryBuilder('s')
            ->addSelect('u')
            ->leftJoin('s.creator', 'u')
            ->orderBy('s.id', 'desc');

        if ($this->user) {
            $builder->where('s.creator = :creator')
                ->setParameter('creator', $this->user);
        }

        $this->paginatedResult = $this->paginator->paginate($builder, $this->page ?: 1);

        return $this->paginatedResult;
    }

    public function getLinks(): string
    {
        return $this->paginationLinks->generateLinks(
            $this->paginatedResult['pages'],
            $this->paginatedResult['page'],
            $this->router->generate($this->route)
        );
    }

    /**
     * Возвращает закешированный результат
     *
     * @return array|null
     */
    public function getFromCache(): ?array
    {
        $key = "$this->cacheKey:$this->page";
        $ttl = $this->cacheTimeInMin * 60;

        return $this->cache->remember($key, $ttl, function () {
            return $this->fetchData();
        });
    }

    /**
     * Задаёт кеширование результата выполнения запроса для сокращения запросов в БД
     *
     * @param string $key Ключ для кеширования
     * @param int $timeInMin Время в минутах
     * @return StatementPagination
     */
    public function setCache(string $key, int $timeInMin = 5): StatementPagination
    {
        $this->cacheKey = $key;
        $this->cacheTimeInMin = $timeInMin;
        $this->shouldCache = true;

        return $this;
    }

    /**
     * Задать пользователя, заявления которого будут отображены
     *
     * @param User|null $user
     * @return StatementPagination
     */
    public function setUser(?User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Инициализация требуемых полей
     *
     * @param string $route
     * @param int $page
     * @return StatementPagination
     */
    public function init(string $route, int $page = 1): self
    {
        $this->route = $route;
        $this->page = $page ?: 1;

        return $this;
    }
}