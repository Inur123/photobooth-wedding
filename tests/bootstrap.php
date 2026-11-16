<?php

$variables = [
    'APP_ENV' => 'testing',
    'APP_CONFIG_CACHE' => dirname(__DIR__).'/bootstrap/cache/config-testing.php',
    'APP_ROUTES_CACHE' => dirname(__DIR__).'/bootstrap/cache/routes-testing.php',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
];

foreach ($variables as $name => $value) {
    putenv("{$name}={$value}");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

require dirname(__DIR__).'/vendor/autoload.php';
