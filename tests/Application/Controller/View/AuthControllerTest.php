<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\View;

use App\Tests\ApplicationTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends ApplicationTestCase
{
    public function testRegisterPage(): void
    {
        $appClient = self::createClient();
        $appClient->request('GET', '/auth/register');

        $this->assertResponseIsSuccessful();
    }

    public function testLogin(): void
    {
        $appClient = self::createClient();
        $appClient->request('GET', '/auth/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLogout(): void
    {
        $appClient = self::createClient();
        $appClient->request('GET', '/auth/logout');

        $this->authorizeAsClient($appClient);

        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
    }

    /**
     * Нельзя заходить на все страницы без аутентификации, кроме главной
     */
    #[DataProvider('getUrlsForAnonymousUsers')]
    public function testAccessDeniedForAnonymousUsers(string $httpMethod, string $url): void
    {
        $appClient = self::createClient();
        $appClient->request($httpMethod, $url);

        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
    }

    /**
     * Клиент может заходить на все страницы, кроме /admin/*
     */
    #[DataProvider('getUrlsForClient')]
    public function testClientCanVisitAllPagesExceptAdmin(string $httpMethod, string $url): void
    {
        $appClient = self::createClient();

        $this->authorizeAsClient($appClient);

        $appClient->request($httpMethod, $url);

        $this->assertResponseIsSuccessful();
    }

    /**
     * Клиент не может заходить на страницы админа /admin/*
     */
    #[DataProvider('getUrlsForClientHeCantVisit')]
    public function testClientCantVisitAdminPages(string $httpMethod, string $url): void
    {
        $appClient = self::createClient();

        $this->authorizeAsClient($appClient);

        $appClient->request($httpMethod, $url);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Админ может заходить на все страницы админа /admin/*
     */
    #[DataProvider('getUrlsForAdmin')]
    public function testAdminCanVisitAdminPages(string $httpMethod, string $url): void
    {
        $appClient = self::createClient();

        $this->authorizeAsAdmin($appClient);

        $appClient->request($httpMethod, $url);

        $this->assertResponseIsSuccessful();
    }

    public static function getUrlsForAnonymousUsers(): Generator
    {
        yield ['GET', '/statements/create'];
        yield ['GET', '/statements/list'];
        yield ['GET', '/admin/statements/list'];
    }

    public static function getUrlsForClient(): Generator
    {
        yield ['GET', '/statements/create'];
        yield ['GET', '/statements/list'];
    }

    public static function getUrlsForClientHeCantVisit(): Generator
    {
        yield ['GET', '/admin/statements/list'];
    }

    public static function getUrlsForAdmin(): Generator
    {
        yield ['GET', '/admin/statements/list'];
    }
}
