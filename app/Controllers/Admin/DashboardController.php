<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DashboardController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return View::admin('dashboard', [
            'pageTitle' => 'Обзор',
            'current' => 'dashboard',
            'counts' => [
                'projects' => (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
                'integrations' => (int) db()->query('SELECT COUNT(*) FROM integrations')->fetchColumn(),
                'hobbies' => (int) db()->query('SELECT COUNT(*) FROM hobbies')->fetchColumn(),
                'certificates' => (int) db()->query('SELECT COUNT(*) FROM certificates')->fetchColumn(),
            ],
        ]);
    }
}
