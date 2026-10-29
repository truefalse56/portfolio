<?php

declare(strict_types=1);

$admin = auth_user();
$current = $current ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($pageTitle ?? 'Админка') . ' — Portfolio') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
<div class="admin-shell d-flex">
  <aside class="sidebar d-none d-lg-flex flex-column" id="adminSidebarDesktop">
    <div class="logo">Portfolio Admin</div>
    <nav class="nav flex-column gap-1">
      <a class="nav-link <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= e(base_url('admin/')) ?>">Обзор</a>
      <a class="nav-link <?= $current === 'projects' ? 'active' : '' ?>" href="<?= e(base_url('admin/projects.php')) ?>">Проекты</a>
      <a class="nav-link <?= $current === 'integrations' ? 'active' : '' ?>" href="<?= e(base_url('admin/integrations.php')) ?>">Интеграции</a>
      <a class="nav-link <?= $current === 'hobbies' ? 'active' : '' ?>" href="<?= e(base_url('admin/hobbies.php')) ?>">Хобби</a>
      <a class="nav-link <?= $current === 'certificates' ? 'active' : '' ?>" href="<?= e(base_url('admin/certificates.php')) ?>">Сертификаты</a>
      <a class="nav-link <?= $current === 'settings' ? 'active' : '' ?>" href="<?= e(base_url('admin/settings.php')) ?>">Настройки</a>
    </nav>
    <div class="side-foot mt-auto">
      <span><?= e($admin['name'] ?? '') ?></span>
      <a href="<?= e(base_url()) ?>" target="_blank" rel="noopener">Открыть сайт</a>
      <a href="<?= e(base_url('admin/logout.php')) ?>">Выйти</a>
    </div>
  </aside>

  <div class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header">
      <h2 class="offcanvas-title logo mb-0" id="adminSidebarLabel">Portfolio Admin</h2>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
      <nav class="nav flex-column gap-1">
        <a class="nav-link <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= e(base_url('admin/')) ?>">Обзор</a>
        <a class="nav-link <?= $current === 'projects' ? 'active' : '' ?>" href="<?= e(base_url('admin/projects.php')) ?>">Проекты</a>
        <a class="nav-link <?= $current === 'integrations' ? 'active' : '' ?>" href="<?= e(base_url('admin/integrations.php')) ?>">Интеграции</a>
        <a class="nav-link <?= $current === 'hobbies' ? 'active' : '' ?>" href="<?= e(base_url('admin/hobbies.php')) ?>">Хобби</a>
        <a class="nav-link <?= $current === 'certificates' ? 'active' : '' ?>" href="<?= e(base_url('admin/certificates.php')) ?>">Сертификаты</a>
        <a class="nav-link <?= $current === 'settings' ? 'active' : '' ?>" href="<?= e(base_url('admin/settings.php')) ?>">Настройки</a>
      </nav>
      <div class="side-foot mt-auto">
        <span><?= e($admin['name'] ?? '') ?></span>
        <a href="<?= e(base_url()) ?>" target="_blank" rel="noopener">Открыть сайт</a>
        <a href="<?= e(base_url('admin/logout.php')) ?>">Выйти</a>
      </div>
    </div>
  </div>

  <div class="content flex-grow-1">
    <div class="topbar d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
      <div class="d-flex align-items-center gap-2">
        <button
          type="button"
          class="btn btn-outline-secondary d-lg-none"
          data-bs-toggle="offcanvas"
          data-bs-target="#adminSidebar"
          aria-controls="adminSidebar"
        >Меню</button>
        <h1 class="mb-0"><?= e($pageTitle ?? 'Админка') ?></h1>
      </div>
      <?php if (!empty($topAction)): ?>
        <?= $topAction ?>
      <?php endif; ?>
    </div>
    <?php foreach (get_flashes() as $flash): ?>
      <div class="alert <?= $flash['type'] === 'ok' ? 'alert-success' : 'alert-danger' ?>" role="alert">
        <?= e($flash['message']) ?>
      </div>
    <?php endforeach; ?>
