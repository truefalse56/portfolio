<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthController
{
    public function loginForm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (auth_user()) {
            return View::redirect(admin_url());
        }

        return View::html(View::render('admin/login', [
            'error' => null,
            'username' => '',
        ]));
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (auth_user()) {
            return View::redirect(admin_url());
        }

        $data = (array) $request->getParsedBody();
        $error = null;
        $username = trim((string) ($data['username'] ?? ''));

        if (!verify_csrf($data['_csrf'] ?? null)) {
            $error = 'Сессия устарела. Обновите страницу.';
        } elseif (!attempt_login($username, (string) ($data['password'] ?? ''))) {
            $error = 'Неверный логин или пароль.';
        } else {
            return View::redirect(admin_url());
        }

        return View::html(View::render('admin/login', [
            'error' => $error,
            'username' => $username,
        ]));
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        logout();
        return View::redirect(admin_url('login'));
    }
}
