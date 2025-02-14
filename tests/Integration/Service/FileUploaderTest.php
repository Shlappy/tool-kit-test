<?php

namespace App\Tests\Integration\Service;

use App\Service\FileUploader;
use App\Tests\ApplicationTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileUploaderTest extends ApplicationTestCase
{
    private CONST string UPLOAD_PATH = __DIR__ . '/../../../var/uploads';

    /**
     * Метод upload сохраняет загруженный клиентом файл и создаёт сущность File
     */
    public function testUploadWorks(): void
    {
        $appClient = self::createClient();
        $container = self::getContainer();

        $this->authorizeAsClient($appClient);

        /** @var FileUploader $fileUploader */
        $fileUploader = $container->get(FileUploader::class);

        // Создаём папку, если ещё не создана
        $filesystem = new Filesystem;
        if (!is_dir(self::UPLOAD_PATH)) {
            $filesystem->mkdir(self::UPLOAD_PATH);
        }

        // Создаём файл
        $result = file_put_contents(self::UPLOAD_PATH . "/upload-test.txt", 'random text inside txt file');
        $this->assertNotFalse($result);

        $uploadedFile = new UploadedFile(
            self::UPLOAD_PATH . '/upload-test.txt',
            'upload-test-2.txt',
            'text/plain',
            \UPLOAD_ERR_OK,
            true
        );

        $file = $fileUploader->upload($uploadedFile);

        // Сущность создалась
        $this->assertNotEmpty($file->getId());
        // Файл сохранился в указанную директорию
        $path = self::UPLOAD_PATH . '/' . $file->getName();
        $this->assertTrue(is_file($path));

        // Удаляем файл, т.к. больше он не нужен
        $filesystem->remove($path);
    }
}