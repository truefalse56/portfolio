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
  <meta name="robots" content="noindex, nofollow">
  <title>Вход — Portfolio Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="login-page">
  <form class="login-card" method="post" autocomplete="on">
    <?= csrf_field() ?>
    <h1>Админ-панель</h1>
    <p>Управление проектами, интеграциями и грамотами.</p>
    <?php if ($error): ?>
      <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">Логин</label>
      <input class="form-control" type="text" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Пароль</label>
      <input class="form-control" type="password" name="password" required>
    </div>
    <button class="btn btn-primary w-100" type="submit">Войти</button>
  </form>
</body>
</html>
