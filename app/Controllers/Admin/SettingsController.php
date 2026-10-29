<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SettingsController
{
    private array $keys = [
        'full_name', 'title', 'subtitle', 'about', 'email', 'telegram', 'phone',
        'github', 'location', 'hero_cta_text', 'hero_cta_link', 'meta_description',
    ];

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->save($request);
        }

        $values = [];
        foreach ($this->keys as $key) {
            $values[$key] = setting($key, '');
        }

        return View::admin('settings', [
            'pageTitle' => 'Настройки профиля',
            'current' => 'settings',
            'values' => $values,
        ]);
    }

    private function save(ServerRequestInterface $request): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('settings'));
        }

        $section = $data['section'] ?? 'profile';

        try {
            if ($section === 'profile') {
                $stmt = db()->prepare(
                    'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
                );
                foreach ($this->keys as $key) {
                    $stmt->execute([$key, trim((string) ($data[$key] ?? ''))]);
                }
                flash('ok', 'Настройки сохранены.');
            }

            if ($section === 'password') {
                $currentPass = (string) ($data['current_password'] ?? '');
                $newPass = (string) ($data['new_password'] ?? '');
                $confirm = (string) ($data['confirm_password'] ?? '');
                $admin = auth_user();

                $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
                $stmt->execute([(int) $admin['id']]);
                $row = $stmt->fetch();

                if (!$row || !password_verify($currentPass, $row['password_hash'])) {
                    throw new \RuntimeException('Текущий пароль неверен.');
                }
                if (strlen($newPass) < 6) {
                    throw new \RuntimeException('Новый пароль — не короче 6 символов.');
                }
                if ($newPass !== $confirm) {
                    throw new \RuntimeException('Подтверждение пароля не совпадает.');
                }

                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                    ->execute([$hash, (int) $admin['id']]);
                flash('ok', 'Пароль обновлён.');
            }
        } catch (\Throwable $e) {
            flash('err', $e->getMessage());
        }

        return View::redirect(admin_url('settings'));
    }
}
