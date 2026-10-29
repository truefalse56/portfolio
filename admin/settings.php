<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$pageTitle = 'Настройки профиля';
$current = 'settings';

$keys = [
    'full_name', 'title', 'subtitle', 'about', 'email', 'telegram', 'phone',
    'github', 'location', 'hero_cta_text', 'hero_cta_link', 'meta_description',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
        redirect(base_url('admin/settings.php'));
    }

    $section = $_POST['section'] ?? 'profile';

    try {
        if ($section === 'profile') {
            $stmt = db()->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach ($keys as $key) {
                $stmt->execute([$key, trim((string) ($_POST[$key] ?? ''))]);
            }
            flash('ok', 'Настройки сохранены.');
        }

        if ($section === 'password') {
            $currentPass = (string) ($_POST['current_password'] ?? '');
            $newPass = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');
            $admin = auth_user();

            $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
            $stmt->execute([(int) $admin['id']]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($currentPass, $row['password_hash'])) {
                throw new RuntimeException('Текущий пароль неверен.');
            }
            if (strlen($newPass) < 6) {
                throw new RuntimeException('Новый пароль — не короче 6 символов.');
            }
            if ($newPass !== $confirm) {
                throw new RuntimeException('Подтверждение пароля не совпадает.');
            }

            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([$hash, (int) $admin['id']]);
            flash('ok', 'Пароль обновлён.');
        }
    } catch (Throwable $e) {
        flash('err', $e->getMessage());
    }

    redirect(base_url('admin/settings.php'));
}

$values = [];
foreach ($keys as $key) {
    $values[$key] = setting($key, '');
}

require __DIR__ . '/_layout_top.php';
?>

<form class="panel mb-4" method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="profile">
  <h2>Публичный профиль</h2>
  <div class="row g-3 mt-1">
    <div class="col-md-6">
      <label class="form-label">ФИО</label>
      <input class="form-control" type="text" name="full_name" value="<?= e($values['full_name']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Должность / роль</label>
      <input class="form-control" type="text" name="title" value="<?= e($values['title']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Подзаголовок</label>
      <input class="form-control" type="text" name="subtitle" value="<?= e($values['subtitle']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">О себе</label>
      <textarea class="form-control" name="about"><?= e($values['about']) ?></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">Email</label>
      <input class="form-control" type="email" name="email" value="<?= e($values['email']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Телефон</label>
      <input class="form-control" type="text" name="phone" value="<?= e($values['phone']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Telegram</label>
      <input class="form-control" type="text" name="telegram" value="<?= e($values['telegram']) ?>" placeholder="@username или ссылка">
    </div>
    <div class="col-md-6">
      <label class="form-label">GitHub</label>
      <input class="form-control" type="url" name="github" value="<?= e($values['github']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Локация</label>
      <input class="form-control" type="text" name="location" value="<?= e($values['location']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Текст кнопки в hero</label>
      <input class="form-control" type="text" name="hero_cta_text" value="<?= e($values['hero_cta_text']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Ссылка кнопки hero</label>
      <input class="form-control" type="text" name="hero_cta_link" value="<?= e($values['hero_cta_link']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Meta description</label>
      <input class="form-control" type="text" name="meta_description" value="<?= e($values['meta_description']) ?>">
    </div>
  </div>
  <div class="mt-4">
    <button class="btn btn-primary" type="submit">Сохранить профиль</button>
  </div>
</form>

<form class="panel" method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="password">
  <h2>Смена пароля</h2>
  <div class="row g-3 mt-1">
    <div class="col-12">
      <label class="form-label">Текущий пароль</label>
      <input class="form-control" type="password" name="current_password" required autocomplete="current-password">
    </div>
    <div class="col-md-6">
      <label class="form-label">Новый пароль</label>
      <input class="form-control" type="password" name="new_password" required minlength="6" autocomplete="new-password">
    </div>
    <div class="col-md-6">
      <label class="form-label">Подтверждение</label>
      <input class="form-control" type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
    </div>
  </div>
  <div class="mt-4">
    <button class="btn btn-primary" type="submit">Обновить пароль</button>
  </div>
</form>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
