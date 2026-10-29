<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CertificateAdminController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\HobbyAdminController;
use App\Controllers\Admin\IntegrationAdminController;
use App\Controllers\Admin\ProjectAdminController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\CertificateController;
use App\Controllers\HobbyController;
use App\Controllers\HomeController;
use App\Controllers\IntegrationController;
use App\Controllers\ProjectController;
use App\Controllers\SeoController;
use App\Middleware\AuthMiddleware;
use App\Support\View;
use Slim\App;

return static function (App $app): void {
    // Legacy redirects (old entry PHP / query URLs)
    $redirects = [
        '/index.php' => '/',
        '/projects.php' => '/projects',
        '/integrations.php' => '/integrations',
        '/hobbies.php' => '/hobbies',
        '/certificates.php' => '/certificates',
        '/admin/login.php' => '/admin/login',
        '/admin/logout.php' => '/admin/logout',
        '/admin/index.php' => '/admin',
        '/admin/projects.php' => '/admin/projects',
        '/admin/integrations.php' => '/admin/integrations',
        '/admin/hobbies.php' => '/admin/hobbies',
        '/admin/certificates.php' => '/admin/certificates',
        '/admin/settings.php' => '/admin/settings',
    ];

    foreach ($redirects as $from => $to) {
        $app->get($from, static fn () => View::redirect(base_url(ltrim($to, '/')), 301));
    }

    $app->get('/project.php', static function ($request) {
        $slug = (string) ($request->getQueryParams()['slug'] ?? '');
        return View::redirect($slug !== '' ? page_url('projects', $slug) : page_url('projects'), 301);
    });
    $app->get('/integration.php', static function ($request) {
        $slug = (string) ($request->getQueryParams()['slug'] ?? '');
        return View::redirect($slug !== '' ? page_url('integrations', $slug) : page_url('integrations'), 301);
    });
    $app->get('/hobby.php', static function ($request) {
        $slug = (string) ($request->getQueryParams()['slug'] ?? '');
        return View::redirect($slug !== '' ? page_url('hobbies', $slug) : page_url('hobbies'), 301);
    });

    $app->get('/admin/project-edit.php', static function ($request) {
        $id = (int) ($request->getQueryParams()['id'] ?? 0);
        return View::redirect($id > 0 ? admin_url('projects/' . $id . '/edit') : admin_url('projects/create'), 301);
    });
    $app->get('/admin/integration-edit.php', static function ($request) {
        $id = (int) ($request->getQueryParams()['id'] ?? 0);
        return View::redirect($id > 0 ? admin_url('integrations/' . $id . '/edit') : admin_url('integrations/create'), 301);
    });
    $app->get('/admin/hobby-edit.php', static function ($request) {
        $id = (int) ($request->getQueryParams()['id'] ?? 0);
        return View::redirect($id > 0 ? admin_url('hobbies/' . $id . '/edit') : admin_url('hobbies/create'), 301);
    });
    $app->get('/admin/certificate-edit.php', static function ($request) {
        $id = (int) ($request->getQueryParams()['id'] ?? 0);
        return View::redirect($id > 0 ? admin_url('certificates/' . $id . '/edit') : admin_url('certificates/create'), 301);
    });

    // Public
    $app->get('/', [HomeController::class, 'index']);
    $app->get('/projects', [ProjectController::class, 'index']);
    $app->get('/projects/{slug}', [ProjectController::class, 'show']);
    $app->get('/integrations', [IntegrationController::class, 'index']);
    $app->get('/integrations/{slug}', [IntegrationController::class, 'show']);
    $app->get('/hobbies', [HobbyController::class, 'index']);
    $app->get('/hobbies/{slug}', [HobbyController::class, 'show']);
    $app->get('/certificates', [CertificateController::class, 'index']);
    $app->get('/robots.txt', [SeoController::class, 'robots']);
    $app->get('/sitemap.xml', [SeoController::class, 'sitemap']);

    // Auth
    $app->get('/admin/login', [AuthController::class, 'loginForm']);
    $app->post('/admin/login', [AuthController::class, 'login']);
    $app->get('/admin/logout', [AuthController::class, 'logout']);

    // Admin (protected)
    $app->group('/admin', function ($group) {
        $group->get('', [DashboardController::class, 'index']);
        $group->get('/', [DashboardController::class, 'index']);

        $group->map(['GET', 'POST'], '/projects', [ProjectAdminController::class, 'index']);
        $group->map(['GET', 'POST'], '/projects/create', [ProjectAdminController::class, 'create']);
        $group->map(['GET', 'POST'], '/projects/{id:[0-9]+}/edit', [ProjectAdminController::class, 'edit']);

        $group->map(['GET', 'POST'], '/integrations', [IntegrationAdminController::class, 'index']);
        $group->map(['GET', 'POST'], '/integrations/create', [IntegrationAdminController::class, 'create']);
        $group->map(['GET', 'POST'], '/integrations/{id:[0-9]+}/edit', [IntegrationAdminController::class, 'edit']);

        $group->map(['GET', 'POST'], '/hobbies', [HobbyAdminController::class, 'index']);
        $group->map(['GET', 'POST'], '/hobbies/create', [HobbyAdminController::class, 'create']);
        $group->map(['GET', 'POST'], '/hobbies/{id:[0-9]+}/edit', [HobbyAdminController::class, 'edit']);

        $group->map(['GET', 'POST'], '/certificates', [CertificateAdminController::class, 'index']);
        $group->map(['GET', 'POST'], '/certificates/create', [CertificateAdminController::class, 'create']);
        $group->map(['GET', 'POST'], '/certificates/{id:[0-9]+}/edit', [CertificateAdminController::class, 'edit']);

        $group->map(['GET', 'POST'], '/settings', [SettingsController::class, 'index']);
    })->add(AuthMiddleware::class);
};
