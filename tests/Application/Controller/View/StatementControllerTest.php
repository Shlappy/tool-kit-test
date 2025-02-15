<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\View;

use App\Entity\Statement;
use App\Enum\Custom\StatementTypes;
use App\Repository\UserRepository;
use App\Tests\ApplicationTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class StatementControllerTest extends ApplicationTestCase
{
    /**
     * Клиент может создавать заявления
     */
    public function testClientCanCreateStatement(): void
    {
        $appClient = self::createClient();

        $this->authorizeAsClient($appClient);

        $data = [
            'statement_create' => [
                'number' => '123456',
                'fullName' => 'igor igorevich',
                'date' => '2009-01-23',
                'phone' => '+7123131231',
                'comment' => 'комментарий комментарий комментарий',
                'typeId' => StatementTypes::FIRST['id'],
            ]
        ];

        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $appClient->getContainer()->get(EntityManagerInterface::class);

        /** @var Statement $lastStatement */
        $lastStatement = $entityManager->createQueryBuilder()
            ->select('s')
            ->from(Statement::class, 's')
            ->orderBy('s.id', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertSame($lastStatement->getNumber(), $data['statement_create']['number']);
    }

    /**
     * Анонимный пользователь не может создавать заявления
     */
    public function testAnonymousCantCreateStatement(): void
    {
        $appClient = self::createClient();

        $data = [
            'statement_create' => [
                'number' => '123456',
                'fullName' => 'igor igorevich',
                'date' => '2009-01-23',
                'phone' => '+7123131231',
                'comment' => 'комментарий комментарий комментарий',
                'typeId' => StatementTypes::FIRST['id'],
            ]
        ];

        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/');
    }

    /**
     * Для клиента в списке отображаются только созданные им заявления
     */
    public function testClientCanSeeOnlyHisOwnStatements(): void
    {
        $appClient = self::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $appClient->getContainer()->get(EntityManagerInterface::class);
        $userRepository = $appClient->getContainer()->get(UserRepository::class);

        $entityManager->createQueryBuilder()
            ->delete(Statement::class, 's')
            ->getQuery()
            ->execute();

        //Авторизуемся как первый клиент
        $this->authorizeAsClient($appClient);

        $data = [
            'statement_create' => [
                'number' => '123456',
                'fullName' => 'igor igorevich',
                'date' => '2009-01-23',
                'phone' => '+7123131231',
                'comment' => 'комментарий комментарий комментарий',
                'typeId' => StatementTypes::FIRST['id'],
            ]
        ];

        // Создаём 3 заявления
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');

        //Авторизуемся как второй клиент
        $this->authorizeAsClient($appClient, [
            'login_form' => [
                'email' => 'client2@test.ru',
                'plainPassword' => '123456'
            ]
        ]);

        // Создаём 2 заявления
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');

        $firstClient = $userRepository->findOneBy(['email' => 'client1@test.ru']);
        $secondClient = $userRepository->findOneBy(['email' => 'client2@test.ru']);

        $firstClientStatements = $entityManager->createQueryBuilder()
            ->select('COUNT(s) AS count')
            ->from(Statement::class, 's')
            ->where('s.creator = :creator')
            ->setParameter('creator', $firstClient)
            ->getQuery()
            ->getSingleScalarResult();

        // У первого клиента 3 заявления в БД
        $this->assertEquals(3, $firstClientStatements);

        $secondClientStatements = $entityManager->createQueryBuilder()
            ->select('COUNT(s) AS count')
            ->from(Statement::class, 's')
            ->where('s.creator = :creator')
            ->setParameter('creator', $secondClient)
            ->getQuery()
            ->getSingleScalarResult();

        // У второго клиента 2 заявления в БД
        $this->assertEquals(2, $secondClientStatements);

        //Авторизуемся как первый клиент
        $this->authorizeAsClient($appClient);

        // При заходе на страницу у первого клиента тоже должно быть только 3 заявления
        $crawler = $appClient->request('GET', '/statements/list', $data);
        $this->assertEquals(3, $crawler->filter('table.statements-table tbody tr')->count());

        // Авторизуемся как второй клиент
        $this->authorizeAsClient($appClient, [
            'login_form' => [
                'email' => 'client2@test.ru',
                'plainPassword' => '123456'
            ]
        ]);

        // При заходе на страницу у второго клиента тоже должно быть только 2 заявления
        $crawler = $appClient->request('GET', '/statements/list', $data);
        $this->assertEquals(2, $crawler->filter('table.statements-table tbody tr')->count());
    }

    /**
     * Для админа в списке отображаются все заявления, созданные клиентами
     */
    public function testAdminCanSeeAllStatements(): void
    {
        $appClient = self::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $entityManager->createQueryBuilder()
            ->delete(Statement::class, 's')
            ->getQuery()
            ->execute();

        //Авторизуемся как первый клиент
        $this->authorizeAsClient($appClient);

        $data = [
            'statement_create' => [
                'number' => '123456',
                'fullName' => 'igor igorevich',
                'date' => '2009-01-23',
                'phone' => '+7123131231',
                'comment' => 'комментарий комментарий комментарий',
                'typeId' => StatementTypes::FIRST['id'],
            ]
        ];

        // Создаём 3 заявления
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');

        //Авторизуемся как второй клиент
        $this->authorizeAsClient($appClient, [
            'login_form' => [
                'email' => 'client2@test.ru',
                'plainPassword' => '123456'
            ]
        ]);

        // Создаём 2 заявления
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');
        $appClient->request('POST', '/statements/create', $data);
        $this->assertResponseRedirects('/statements/list');

        // Авторизуемся как админ
        $this->authorizeAsAdmin($appClient);

        // При заходе на страницу должно быть 5 заявлений
        $crawler = $appClient->request('GET', '/admin/statements/list', $data);
        $this->assertEquals(5,  $crawler->filter('table.statements-table tbody tr')->count());
    }
}
