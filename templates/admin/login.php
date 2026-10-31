<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Вход — Portfolio Admin</title>
  <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap/bootstrap.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="login-page">
  <form class="login-card" method="post" autocomplete="on">
    <?= csrf_field() ?>
    <h1>Админ-панель</h1>
    <p>Управление проектами, интеграциями и грамотами.</p>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">Логин</label>
      <input class="form-control" type="text" name="username" required autofocus value="<?= e($username ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Пароль</label>
      <input class="form-control" type="password" name="password" required>
    </div>
    <button class="btn btn-primary w-100" type="submit">Войти</button>
  </form>
</body>
</html>
