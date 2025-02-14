<?php

namespace App\EventListener;

use App\Entity\Statement;
use App\Enum\CacheKeys;
use App\Service\Cache\AppCacheInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Statement::class)]
readonly class StatementListener
{
    public function __construct(private AppCacheInterface $cache)
    {
    }

    /**
     * При создании нового заявления удаляем кеш для админа, чтобы он мог увидеть свежие записи
     *
     * @return void
     */
    public function postPersist(): void
    {
        // В реальном проекте нужно было бы писать lua-скрипт для выборки по паттерну "admin_statement_list.*"
        // Для экономии времени было реализовано по-простому.
        for ($page = 1; $page < 1000; $page++) {
            if ($this->cache->delete(CacheKeys::ADMIN_STATEMENT_LIST->value . ":$page") === 0) {
                break;
            }
        }
    }
}