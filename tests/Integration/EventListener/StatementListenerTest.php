<?php

namespace App\Tests\Integration\EventListener;

use App\EventListener\StatementListener;
use App\Tests\ApplicationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

final class StatementListenerTest extends ApplicationTestCase
{
    /**
     * StatementListener вызывается при событии postPersist
     */
    public function testStatementListenerInvokes() : void
    {
        // $appClient = self::createClient();
        $container = self::getContainer();

        $mockedListener = $this->createMock(StatementListener::class);
        // Метод вызвается 1 раз и ничего не возвращает
        $mockedListener->expects($this->once())->method('postPersist');
        $container->set(StatementListener::class, $mockedListener);

        /** @var StatementListener $listener */
        $listener = $container->get(StatementListener::class);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $eventManager = $entityManager->getEventManager();
        $eventManager->addEventListener(Events::postPersist, $listener);
        $eventManager->dispatchEvent(Events::postPersist);
    }
}