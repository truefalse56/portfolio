<?php

declare(strict_types=1);

/**
 * Точка входа для подключения к БД.
 * Рабочие доступы лежат только в database.local.php и в git не попадают.
 * Этот файл можно заливать на сервер — локальный конфиг он не перезаписывает.
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
