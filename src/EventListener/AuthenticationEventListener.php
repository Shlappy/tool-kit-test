<?php declare(strict_types=1);

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * Слушатель для событий аутентификации
 */
final class AuthenticationEventListener
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    #[AsEventListener(Events::JWT_INVALID)]
    public function onJWTInvalidEvent(JWTInvalidEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('home')));
        // Очистка токена из куки браузера
        $event->getResponse()->headers->clearCookie('BEARER');
    }
}