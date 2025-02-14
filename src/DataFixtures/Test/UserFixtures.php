<?php declare(strict_types=1);

namespace App\DataFixtures\Test;

use App\Entity\User;
use App\Enum\Roles;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullname, $password, $email, $roles, $phone, $address, $birthDate]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setPhone($phone);
            $user->setAddress($address);
            $user->setBirthDate($birthDate);

            $manager->persist($user);

            $this->addReference($email, $user);
        }

        $manager->flush();
    }

    /**
     * @return array<array>
     */
    private function getUserData(): array
    {
        return [
            [
                'Тестовый Тестович',
                '12345678',
                'admin@test.ru',
                [Roles::ADMIN->value],
                '+71234567893',
                'г. Москва, ул. Ленина, д. 37, кв. 32',
                new DateTime('1998-01-05')
            ],
            [
                'Обычный Человек',
                '12345678',
                'client1@test.ru',
                [Roles::CLIENT->value],
                '+79873218763',
                'г. Владивосток, ул. Ленина, д. 31, кв. 31',
                new DateTime('2005-02-05')
            ],
            [
                'Игорь Безупречный Игоревич',
                '12345678',
                'client2@test.ru',
                [Roles::CLIENT->value],
                '+75023847525',
                'г. Самара, ул. Ленина, д. 32, кв. 30',
                new DateTime('1978-03-25')
            ],
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }
}