<?php

declare(strict_types=1);

function auth_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT id, username, name FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $user = $stmt->fetch() ?: null;

    if (!$user) {
        unset($_SESSION['admin_id']);
    }

    return $user ?: null;
}

function require_auth(): void
{
    if (!auth_user()) {
        redirect(admin_url('login'));
    }
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, username, password_hash, name FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    return true;
}

function logout(): void
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}
