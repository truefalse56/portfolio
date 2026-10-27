<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

if (auth_user()) {
    redirect(base_url('admin/'));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        $error = 'Сессия устарела. Обновите страницу.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (attempt_login($username, $password)) {
            redirect(base_url('admin/'));
        }
        $error = 'Неверный логин или пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Вход — Portfolio Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="login-page">
  <form class="login-card" method="post" autocomplete="on">
    <?= csrf_field() ?>
    <h1>Админ-панель</h1>
    <p>Управление проектами, интеграциями и грамотами.</p>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <label>Логин</label>
    <input type="text" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
    <label>Пароль</label>
    <input type="password" name="password" required>
    <p style="margin-top:1.25rem"><button class="btn" type="submit" style="width:100%">Войти</button></p>
  </form>
</body>
</html>
