<?php declare(strict_types=1);

namespace App\Controller\API\Users;

use App\Controller\BaseController;
// use App\Dto\User\UserCreateDto;
// use App\Entity\User;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// use Symfony\Component\Routing\Annotation\Route;

class AuthenticateController extends BaseController
{
    // #[Route('/api/register')]
    // public function register(
    //     #[MapRequestPayload] UserCreateDto $userData,
    //     UserPasswordHasherInterface $passwordHasher,
    //     EntityManagerInterface $entityManager
    // ): Response
    // {
    //     $user = new User(email: $userData->email);

    //     $hashedPassword = $passwordHasher->hashPassword($user, $userData->password);
    //     $user->setPassword($hashedPassword);

    //     //$validator->validate($user);

    //     $entityManager->persist($user);
    //     $entityManager->flush();

    //     return $this->json([], Response::HTTP_OK);
    // }

    // // #[Route('/api/logout')]
    // // public function logout(UserPasswordHasherInterface $passwordHasher): Response
    // // {

    // // }
}