<?php

namespace App\Tests\Integration\Service;

use App\DataFixtures\Traits\FakeGenerator;
use App\Entity\Statement;
use App\Repository\UserRepository;
use App\Service\Pagination\StatementPagination;
use App\Tests\ApplicationTestCase;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class StatementPaginationTest extends ApplicationTestCase
{
    use FakeGenerator;

    /**
     * Пагинация заявлений работает корректно
     */
    public function testPaginationWorks(): void
    {
        $appClient = self::createClient();
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $appClient->getContainer()->get(EntityManagerInterface::class);
        $userRepository = $appClient->getContainer()->get(UserRepository::class);
        $creator = $userRepository->findOneBy(['email' => 'client1@test.ru']);

        // Авторизуемся как клиент
        $this->authorizeAsClient($appClient);

        $entityManager->createQueryBuilder()
            ->delete(Statement::class, 's')
            ->getQuery()
            ->execute();

        // Загружаем 50 заявлений
        for ($i = 0; $i < 50; $i++) {
            $statement = new Statement;
            $statement->setNumber((string)rand(1, 99999));
            $statement->setDate($this->randomDateInRange(new DateTime('1995-01-01'), new DateTime('2025-01-01')));
            $statement->setFullName($this->randomText());
            $statement->setComment($this->randomText());
            $statement->setPhone($this->randomPhone());
            $statement->setCreator($creator);

            $entityManager->persist($statement);
        }
        $entityManager->flush();

        /** @var StatementPagination $statementPagination */
        $statementPagination = $container->get(StatementPagination::class);
        $statementPagination->init('statement_list')->setUser($creator);

        // Какой должен быть результат пагинации при page = 1
        $whatMustBe = [
            'total' => 50,
            'page' => 1,
            'limit' => 10,
            'pages' => 5.0,
        ];

        $results = $statementPagination->getResult();
        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($whatMustBe, $results, ['items']);
        $this->assertCount(10, $results['items']);

        $statementPagination->init('statement_list', 5)->setUser($creator);

        // Какой должен быть результат пагинации при page = 5
        $whatMustBeSecond = [
            'total' => 50,
            'page' => 5,
            'limit' => 10,
            'pages' => 5.0,
        ];

        $resultsSecond = $statementPagination->getResult();

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($whatMustBeSecond, $resultsSecond, ['items']);
        $this->assertCount(10, $results['items']);
    }
}