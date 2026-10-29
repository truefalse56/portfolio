<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/bootstrap.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();

// Shared hosting / subdirectory: detect base path from SCRIPT_NAME
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = str_replace('\\', '/', dirname($scriptName));
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}
if ($basePath !== '') {
    $app->setBasePath($basePath);
}

$app->addBodyParsingMiddleware();

$isDebug = is_file(__DIR__ . '/storage/debug.flag');
$errorMiddleware = $app->addErrorMiddleware($isDebug, true, true);
$errorMiddleware->setDefaultErrorHandler(
    static function ($request, Throwable $exception, bool $displayErrorDetails) use ($app) {
        $logDir = __DIR__ . '/storage';
        if (is_dir($logDir) && is_writable($logDir)) {
            @file_put_contents(
                $logDir . '/error.log',
                '[' . date('c') . '] ' . $exception->getMessage()
                . ' @ ' . $exception->getFile() . ':' . $exception->getLine() . "\n"
                . $exception->getTraceAsString() . "\n\n",
                FILE_APPEND
            );
        }

        $response = $app->getResponseFactory()->createResponse(500);
        $body = '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Ошибка</title></head><body>'
            . '<h1>Ошибка сервера</h1><p>Попробуйте обновить страницу позже.</p>';
        if ($displayErrorDetails) {
            $body .= '<pre>' . htmlspecialchars(
                $exception->getMessage() . "\n" . $exception->getFile() . ':' . $exception->getLine(),
                ENT_QUOTES,
                'UTF-8'
            ) . '</pre>';
        }
        $body .= '</body></html>';
        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
);

$routes = require __DIR__ . '/app/routes.php';
$routes($app);

$app->run();
