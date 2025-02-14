<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\API;

use App\Entity\File;
use App\Repository\UserRepository;
use App\Tests\ApplicationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

final class FileControllerTest extends ApplicationTestCase
{
    private CONST string UPLOAD_PATH = __DIR__ . '/../../../../var/uploads';

    /**
     * Клиент может скачивать свой файл и не может скачивать файлы других клиентов
     */
    public function testClientCanDownloadItsFileButNotOthers(): void
    {
        $appClient = self::createClient();

        $this->authorizeAsClient($appClient);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $appClient->getContainer()->get(EntityManagerInterface::class);
        /** @var UserRepository $userRepository */
        $userRepository = $appClient->getContainer()->get(UserRepository::class);

        $firstClient = $userRepository->findOneBy([
            'email' => $this->getDefaultClientLoginData()['login_form']['email']
        ]);

        // Создаём папку, если ещё не создана
        $filesystem = new Filesystem;
        if (!is_dir(self::UPLOAD_PATH)) {
            $filesystem->mkdir(self::UPLOAD_PATH);
        }

        // Создаём файл
        $filename = 'file-for-test.txt';
        $result = file_put_contents(self::UPLOAD_PATH . "/$filename", 'random text inside txt file');
        $this->assertNotFalse($result);

        $firstFile = (new File)->setCreator($firstClient)->setName($filename);
        $entityManager->persist($firstFile);
        $entityManager->flush();

        $appClient->request('GET', "/files/{$firstFile->getId()}");
        $this->assertResponseIsSuccessful();


        //Авторизуемся как второй клиент
        $this->authorizeAsClient($appClient, [
            'login_form' => [
                'email' => 'client2@test.ru',
                'plainPassword' => '12345678'
            ]
        ]);
        $secondClient = $userRepository->findOneBy(['email' => 'client2@test.ru']);

        // Создаём файл
        $secondFilename = 'file-for-test-2.txt';
        file_put_contents(self::UPLOAD_PATH . "/$secondFilename", 'random text inside second txt file');
        $secondFile = (new File)->setCreator($secondClient)->setName($secondFilename);
        $entityManager->persist($secondFile);
        $entityManager->flush();

        $appClient->request('GET', "/files/{$secondFile->getId()}");
        $this->assertResponseIsSuccessful();

        // Второй клиент не может скачивать файл первого клиента
        $appClient->request('GET', "/files/{$firstFile->getId()}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Админ может скачивать файл всех клиентов
     */
    public function testClientCantDownloadItsFile(): void
    {
        $appClient = self::createClient();

        $this->authorizeAsAdmin($appClient);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $appClient->getContainer()->get(EntityManagerInterface::class);
        /** @var UserRepository $userRepository */
        $userRepository = $appClient->getContainer()->get(UserRepository::class);

        $firstClient = $userRepository->findOneBy([
            'email' => $this->getDefaultClientLoginData()['login_form']['email']
        ]);

        // Создаём папку, если ещё не создана
        $filesystem = new Filesystem;
        if (!is_dir(self::UPLOAD_PATH)) {
            $filesystem->mkdir(self::UPLOAD_PATH);
        }

        // Создаём второй первый файл
        $filename = 'file-for-test.txt';
        file_put_contents(self::UPLOAD_PATH . "/$filename", 'random text inside txt file');
        $firstFile = (new File)->setCreator($firstClient)->setName($filename);
        $entityManager->persist($firstFile);
        $entityManager->flush();

        $secondClient = $userRepository->findOneBy(['email' => 'client2@test.ru']);

        // Создаём файл
        $secondFilename = 'file-for-test-2.txt';
        file_put_contents(self::UPLOAD_PATH . "/$secondFilename", 'random text inside second txt file');
        $secondFile = (new File)->setCreator($secondClient)->setName($secondFilename);
        $entityManager->persist($secondFile);
        $entityManager->flush();

        // Админ может скачать файл, созданный первым клиентом
        $appClient->request('GET', "/files/{$firstFile->getId()}");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Админ может скачать файл, созданный вторым клиентом
        $appClient->request('GET', "/files/{$secondFile->getId()}");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
