<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Statement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Statement>
 */
class StatementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statement::class);
    }

    /**
     * Добавить новое заявление
     */
    public function create(Statement $statement): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($statement);
        $entityManager->flush();
    }
}
