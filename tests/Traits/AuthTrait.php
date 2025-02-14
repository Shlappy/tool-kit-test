<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;

trait AuthTrait
{
    /**
     * Авторизация за клиента
     *
     * @param KernelBrowser $client
     * @param array|null $loginData
     * @return void
     */
    protected function authorizeAsClient(KernelBrowser $client, ?array $loginData = null): void
    {
        $token = $this->getJWTToken($client, $loginData ?: $this->getDefaultClientLoginData());

        $cookie = new Cookie('BEARER', $token);
        $client->getCookieJar()->set($cookie);
    }

    /**
     * Авторизация за админа
     *
     * @param KernelBrowser $client
     * @return void
     */
    protected function authorizeAsAdmin(KernelBrowser $client): void
    {
        $token = $this->getJWTToken($client, $this->getDefaultAdminLoginData());

        $cookie = new Cookie('BEARER', $token);
        $client->getCookieJar()->set($cookie);
    }

    /**
     * Делает запрос на получение токена и возвращает его
     *
     * @param KernelBrowser $client
     * @param array|null $loginData
     * @return string|null
     */
    protected function getJWTToken(KernelBrowser $client, ?array $loginData = null): ?string
    {
        $client->request('POST', '/auth/login', $loginData ?: $this->getDefaultClientLoginData());

        $token = $client->getResponse()->headers->getCookies()[0]->getValue() ?? null;
        $this->assertNotEmpty($token);

        return $token;
    }

    protected function getDefaultAdminLoginData(): array
    {
        return [
            'login_form' => [
                'email' => 'admin@test.ru',
                'plainPassword' => '12345678'
            ]
        ];
    }

    protected function getDefaultClientLoginData(): array
    {
        return [
            'login_form' => [
                'email' => 'client1@test.ru',
                'plainPassword' => '12345678'
            ]
        ];
    }
}