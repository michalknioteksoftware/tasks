<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (!class_exists(Dotenv::class)) {
    throw new LogicException('Please run "composer require symfony/dotenv" to load the ".env" files.');
}

(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

$_SERVER += $_ENV += [
    'APP_ENV' => $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev',
    'APP_DEBUG' => $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? '1',
];
