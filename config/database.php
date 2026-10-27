<?php

declare(strict_types=1);

/**
 * Точка входа для подключения к БД.
 * Рабочие доступы хранятся в database.local.php (создаёт install/fix-database).
 * Этот файл можно безопасно заливать на сервер — локальный конфиг не затрётся.
 */
$local = __DIR__ . '/database.local.php';
if (is_file($local)) {
    return require $local;
}

return [
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => '',
    'username' => '',
    'password' => '',
    'charset'  => 'utf8mb4',
];
