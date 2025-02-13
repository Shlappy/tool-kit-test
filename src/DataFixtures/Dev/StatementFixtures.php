<?php declare(strict_types=1);

namespace App\DataFixtures\Dev;

use App\DataFixtures\Traits\FakeGenerator;
use App\Entity\File;
use App\Entity\Statement;
use App\Entity\User;
use App\Enum\Custom\StatementTypes;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

class StatementFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use FakeGenerator;

    private CONST UPLOAD_PATH = __DIR__ . '/../../../var/uploads';

    public function load(ObjectManager $manager): void
    {
        // Удаляем папку загрузок, если есть, и создаём новую
        $filesystem = new Filesystem;
        if (!is_dir(self::UPLOAD_PATH)) {
            $filesystem->remove(self::UPLOAD_PATH);
            $filesystem->mkdir(self::UPLOAD_PATH);
        } else {
            $filesystem->mkdir(self::UPLOAD_PATH);
        }

        foreach ($this->getStatementData() as [$number, $date, $fullName, $comment, $phone, $typeId, $creator, $file]) {
            $statement = new Statement;
            $statement->setNumber((string)$number);
            $statement->setDate($date);
            $statement->setFullName($fullName);
            $statement->setComment($comment);
            $statement->setPhone($phone);
            $statement->setTypeId($typeId);
            $statement->setCreator($creator);
            $statement->setFile($file);
            
            $manager->persist($statement);
        }

        $manager->flush();
    }

    /**
     * @return array<array>
     */
    private function getStatementData(): array
    {
        $data = [];
        $typeIds = [StatementTypes::FIRST['id'], StatementTypes::REPEATED['id']];

        for ($i = 0; $i < 50; $i++) {
            $emails = ['client1@test.ru', 'client2@test.ru'];
            $creator = $this->getReference($emails[array_rand($emails)], User::class);

            // Создаём файл
            $filename = uniqid() . '-fixture.txt';

            file_put_contents(self::UPLOAD_PATH . "/$filename", $this->getFileText());

            $data[] = [
                rand(1, 99999),
                $this->randomDateInRange(new DateTime('1995-01-01'), new DateTime('2025-01-01')),
                $this->randomText(),
                $this->randomText(),
                $this->randomPhone(),
                $typeIds[array_rand($typeIds)],
                $creator,
                (new File)->setCreator($creator)->setName($filename)
            ];
        }

        return $data;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }
}