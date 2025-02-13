<?php declare(strict_types=1);

namespace App\DataFixtures\Prod;

use App\Entity\User;
use App\Enum\Roles;
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
        foreach ($this->getUserData() as [$fullname, $password, $email, $roles]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);

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
                'Админ Админов Админович',
                '12345678',
                'admin@test.ru',
                [Roles::ADMIN->value],
            ],
            [
                'Клиент Клиентов Клиентович',
                '12345678',
                'client@test.ru',
                [Roles::CLIENT->value],
            ],
        ];
    }

    public static function getGroups(): array
    {
        return ['prod'];
    }
}