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

<form class="panel" method="post" style="margin-bottom:1.25rem">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="profile">
  <h2>Публичный профиль</h2>
  <div class="form-grid">
    <div>
      <label>ФИО</label>
      <input type="text" name="full_name" value="<?= e($values['full_name']) ?>">
    </div>
    <div>
      <label>Должность / роль</label>
      <input type="text" name="title" value="<?= e($values['title']) ?>">
    </div>
    <div class="full">
      <label>Подзаголовок</label>
      <input type="text" name="subtitle" value="<?= e($values['subtitle']) ?>">
    </div>
    <div class="full">
      <label>О себе</label>
      <textarea name="about"><?= e($values['about']) ?></textarea>
    </div>
    <div>
      <label>Email</label>
      <input type="email" name="email" value="<?= e($values['email']) ?>">
    </div>
    <div>
      <label>Телефон</label>
      <input type="text" name="phone" value="<?= e($values['phone']) ?>">
    </div>
    <div>
      <label>Telegram</label>
      <input type="text" name="telegram" value="<?= e($values['telegram']) ?>" placeholder="@username или ссылка">
    </div>
    <div>
      <label>GitHub</label>
      <input type="url" name="github" value="<?= e($values['github']) ?>">
    </div>
    <div>
      <label>Локация</label>
      <input type="text" name="location" value="<?= e($values['location']) ?>">
    </div>
    <div>
      <label>Текст кнопки в hero</label>
      <input type="text" name="hero_cta_text" value="<?= e($values['hero_cta_text']) ?>">
    </div>
    <div>
      <label>Ссылка кнопки hero</label>
      <input type="text" name="hero_cta_link" value="<?= e($values['hero_cta_link']) ?>">
    </div>
    <div class="full">
      <label>Meta description</label>
      <input type="text" name="meta_description" value="<?= e($values['meta_description']) ?>">
    </div>
  </div>
  <div class="actions" style="margin-top:1.25rem">
    <button class="btn" type="submit">Сохранить профиль</button>
  </div>
</form>

<form class="panel" method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="password">
  <h2>Смена пароля</h2>
  <div class="form-grid">
    <div class="full">
      <label>Текущий пароль</label>
      <input type="password" name="current_password" required autocomplete="current-password">
    </div>
    <div>
      <label>Новый пароль</label>
      <input type="password" name="new_password" required minlength="6" autocomplete="new-password">
    </div>
    <div>
      <label>Подтверждение</label>
      <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
    </div>
  </div>
  <div class="actions" style="margin-top:1.25rem">
    <button class="btn" type="submit">Обновить пароль</button>
  </div>
</form>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
