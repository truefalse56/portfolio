<?php

declare(strict_types=1);

$appConfig = require dirname(__DIR__) . '/config/app.php';

date_default_timezone_set($appConfig['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($appConfig['session_name']);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}
