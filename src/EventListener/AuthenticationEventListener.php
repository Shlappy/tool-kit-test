<?php declare(strict_types=1);

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * Слушатель для событий аутентификации
 */
readonly class AuthenticationEventListener
{
    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * При неверном или истекшем токене очищается куки BEARER из браузера
     *
     * @param mixed $event
     * @return void
     */
    #[AsEventListener(Events::JWT_NOT_FOUND)]
    #[AsEventListener(Events::JWT_INVALID)]
    #[AsEventListener(Events::JWT_EXPIRED)]
    public function onJWTInvalidEvent(mixed $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('home')));
        // Очистка токена из куки браузера
        $event->getResponse()->headers->clearCookie('BEARER');
    }
}