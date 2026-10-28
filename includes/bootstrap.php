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

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/migrate.php';

// Подсказка, если сайт ещё не установлен
$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($script !== 'install.php' && !is_file(base_path('storage/installed.lock'))) {
    $installUrl = htmlspecialchars(base_url('install.php'), ENT_QUOTES, 'UTF-8');
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Требуется установка</title>'
        . '<style>body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;background:#f3f6f8;color:#14212b}'
        . '.box{max-width:420px;padding:2rem;background:#fff;border:1px solid #d5dee6}a{color:#0f766e}</style></head><body>'
        . '<div class="box"><h1>Сайт ещё не установлен</h1>'
        . '<p>Откройте мастер установки и создайте базу MySQL.</p>'
        . '<p><a href="' . $installUrl . '">Перейти к install.php</a></p></div></body></html>';
    exit;
}

if (is_file(base_path('storage/installed.lock'))) {
    run_migrations();
}
