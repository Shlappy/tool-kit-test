<?php

namespace App\Tests\Integration\EventListener;

use App\EventListener\AuthenticationEventListener;
use App\Tests\ApplicationTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class AuthenticationEventListenerTest extends ApplicationTestCase
{
    /**
     * AuthenticationEventListener вызывается при событиях JWT_NOT_FOUND, JWT_INVALID, JWT_EXPIRED
     */
    public function testStatementListenerInvokes() : void
    {
        $container = self::getContainer();

        $mockedListener = $this->createMock(AuthenticationEventListener::class);
        // Метод вызвается 1 раз и ничего не возвращает
        $mockedListener->expects($this->once())->method('onJWTInvalidEvent');
        $container->set(AuthenticationEventListener::class, $mockedListener);

        $exception = new MissingTokenException('JWT Token not found', 0, new AuthenticationException);
        $event = new JWTNotFoundEvent($exception);

        $eventDispatcher = $container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($event, Events::JWT_NOT_FOUND);
    }
}