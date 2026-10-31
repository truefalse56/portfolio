<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require base_path('config/database.php');

    if (($config['dbname'] ?? '') === '' || ($config['username'] ?? '') === '') {
        db_config_error(
            'Не заданы доступы к MySQL.',
            'Проверьте config/database.local.php.'
        );
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset'] ?? 'utf8mb4'
    );

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        if (PHP_SAPI === 'cli') {
            throw $e;
        }

        db_config_error(
            'Не удалось подключиться к MySQL.',
            'Проверьте доступы в config/database.local.php.'
        );
    }

    return $pdo;
}

function db_config_error(string $title, string $message): never
{
    if (PHP_SAPI === 'cli') {
        throw new RuntimeException($title . ' ' . $message);
    }

    http_response_code(500);
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . $safeTitle . '</title>'
        . '<style>body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;background:#f3f6f8;color:#14212b;padding:24px}'
        . '.box{max-width:520px;padding:2rem;background:#fff;border:1px solid #d5dee6;line-height:1.5}</style></head><body>'
        . '<div class="box"><h1>' . $safeTitle . '</h1><p>' . $safeMessage . '</p></div></body></html>';
    exit;
}
