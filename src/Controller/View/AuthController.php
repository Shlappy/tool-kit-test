<?php

namespace App\Controller\View;

use App\Controller\BaseController;
use App\Entity\User;
use App\Enum\Roles;
use App\Form\Type\LoginFormType;
use App\Form\Type\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security as OASecurity;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthController extends BaseController
{
    public function __construct(private Security $security)
    {
    }

    /**
     * Регистрация клиентов.
     * GET  - страница с формой.
     * POST - отправка данных для регистрации.
     */
    #[Route('/auth/register', name: 'app_register', methods: ['GET', 'POST'])]
    #[OA\Response(
        response: 200,
        description: 'Регистрация клиентов'
    )]
    #[OA\Response(
        response: 422,
        description: 'Неверные данные'
    )]
    #[OA\Tag(name: 'Пользователи')]
    #[OASecurity(name: 'Bearer')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = new User;
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->addRole(Roles::CLIENT->value);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->loginAndRedirectResponse($user);
        }

        return $this->render('views/auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Вход в профиль.
     * GET  - страница с формой.
     * POST - отправка данных для входа.
     */
    #[Route('/auth/login', name: 'app_login', methods: ['GET', 'POST'])]
    #[OA\Response(
        response: 200,
        description: 'Вход в профиль'
    )]
    #[OA\Response(
        response: 422,
        description: 'Неверные данные'
    )]
    #[OA\Tag(name: 'Пользователи')]
    #[OASecurity(name: 'Bearer')]
    public function login(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $userData = $form->getData();

            $repository = $entityManager->getRepository(User::class);
            $user = $repository->findOneByField('email', $userData['email']);

            if ($user && $userPasswordHasher->isPasswordValid($user, $plainPassword)) {
                return $this->loginAndRedirectResponse($user);
            }

            $form->addError(new FormError('Неверный пароль или логин'));
        }

        return $this->render('views/auth/login.html.twig', [
            'loginForm' => $form,
        ]);
    }

    /**
     * Выход из профиля
     */
    #[Route(path: '/auth/logout', name: 'app_logout', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Выход из профиля'
    )]
    #[OA\Tag(name: 'Пользователи')]
    #[OASecurity(name: 'Bearer')]
    public function logout(
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage
    ): RedirectResponse
    {
        $eventDispatcher->dispatch(new LogoutEvent($request, $tokenStorage->getToken()));

        $redirectResponse = $this->redirectToRoute('home');
        $redirectResponse->headers->clearCookie('BEARER');
        
        return $redirectResponse;
    }

    /**
     * Позволяет задать JWT-токен в куки и перенаправить на нужную страницу
     */
    private function loginAndRedirectResponse(User $user): RedirectResponse
    {
        $loginReponse = $this->security->login($user, 'json_login', 'login');
        $redirectResponse = new RedirectResponse($this->generateUrl('home'));
        // Помещаем куки BEARER в loginResponse
        $redirectResponse->headers->setCookie($loginReponse->headers->getCookies()[0]);
        
        return $redirectResponse;
    }
}
