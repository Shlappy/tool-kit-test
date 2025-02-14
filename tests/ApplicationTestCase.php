<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Traits\AuthTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;

abstract class ApplicationTestCase extends BaseTestCase
{
    use AuthTrait;
}
