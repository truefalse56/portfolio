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
  <title><?= e(($pageTitle ?? 'Админка') . ' — Portfolio') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="sidebar" data-sidebar>
    <div class="logo">Portfolio Admin</div>
    <nav>
      <a href="<?= e(base_url('admin/')) ?>" class="<?= $current === 'dashboard' ? 'is-active' : '' ?>">Обзор</a>
      <a href="<?= e(base_url('admin/projects.php')) ?>" class="<?= $current === 'projects' ? 'is-active' : '' ?>">Проекты</a>
      <a href="<?= e(base_url('admin/integrations.php')) ?>" class="<?= $current === 'integrations' ? 'is-active' : '' ?>">Интеграции</a>
      <a href="<?= e(base_url('admin/hobbies.php')) ?>" class="<?= $current === 'hobbies' ? 'is-active' : '' ?>">Хобби</a>
      <a href="<?= e(base_url('admin/certificates.php')) ?>" class="<?= $current === 'certificates' ? 'is-active' : '' ?>">Сертификаты</a>
      <a href="<?= e(base_url('admin/settings.php')) ?>" class="<?= $current === 'settings' ? 'is-active' : '' ?>">Настройки</a>
    </nav>
    <div class="side-foot">
      <span><?= e($admin['name'] ?? '') ?></span>
      <a href="<?= e(base_url()) ?>" target="_blank" rel="noopener">Открыть сайт</a>
      <a href="<?= e(base_url('admin/logout.php')) ?>">Выйти</a>
    </div>
  </aside>
  <div class="content">
    <div class="topbar">
      <div>
        <button type="button" class="mobile-toggle" data-sidebar-toggle>Меню</button>
        <h1><?= e($pageTitle ?? 'Админка') ?></h1>
      </div>
      <?php if (!empty($topAction)): ?>
        <?= $topAction ?>
      <?php endif; ?>
    </div>
    <?php foreach (get_flashes() as $flash): ?>
      <div class="flash <?= e($flash['type'] === 'ok' ? 'ok' : 'err') ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
