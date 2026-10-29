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

$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$errorMiddleware->setDefaultErrorHandler(
    static function ($request, Throwable $exception, bool $displayErrorDetails) use ($app) {
        $response = $app->getResponseFactory()->createResponse(500);
        $response->getBody()->write(
            '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Ошибка</title></head><body>'
            . '<h1>Ошибка сервера</h1><p>Попробуйте обновить страницу позже.</p>'
            . ($displayErrorDetails ? '<pre>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>' : '')
            . '</body></html>'
        );
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
);

$routes = require __DIR__ . '/app/routes.php';
$routes($app);

$app->run();
