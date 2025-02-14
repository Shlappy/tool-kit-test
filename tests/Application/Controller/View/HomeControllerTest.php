<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\View;

use App\Tests\ApplicationTestCase;

class HomeControllerTest extends ApplicationTestCase
{
    /**
     * Проверка работоспособности главной страницы
     */
    public function testHomePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }
}
