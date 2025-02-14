<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:create --if-not-exists --env=test --no-interaction',
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:migrations:migrate --env=test --no-interaction',
    __DIR__
));
passthru(sprintf(
    'php "%s/../bin/console" doctrine:fixtures:load --env=test --group=test --no-interaction',
    __DIR__
));

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php'))
{
    require dirname(__DIR__) . '/config/bootstrap.php';
}
elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}
