<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$pageTitle = 'Обзор';
$current = 'dashboard';

$counts = [
    'projects' => (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    'integrations' => (int) db()->query('SELECT COUNT(*) FROM integrations')->fetchColumn(),
    'hobbies' => (int) db()->query('SELECT COUNT(*) FROM hobbies')->fetchColumn(),
    'certificates' => (int) db()->query('SELECT COUNT(*) FROM certificates')->fetchColumn(),
];

require __DIR__ . '/_layout_top.php';
?>

<div class="stats">
  <div class="stat"><span>Проекты</span><strong><?= $counts['projects'] ?></strong></div>
  <div class="stat"><span>Интеграции</span><strong><?= $counts['integrations'] ?></strong></div>
  <div class="stat"><span>Хобби</span><strong><?= $counts['hobbies'] ?></strong></div>
  <div class="stat"><span>Сертификаты</span><strong><?= $counts['certificates'] ?></strong></div>
</div>

<div class="panel">
  <h2>Быстрые действия</h2>
  <div class="actions" style="margin-top:1rem">
    <a class="btn" href="<?= e(base_url('admin/project-edit.php')) ?>">+ Проект</a>
    <a class="btn" href="<?= e(base_url('admin/integration-edit.php')) ?>">+ Интеграция</a>
    <a class="btn" href="<?= e(base_url('admin/hobby-edit.php')) ?>">+ Хобби</a>
    <a class="btn" href="<?= e(base_url('admin/certificate-edit.php')) ?>">+ Сертификат</a>
    <a class="btn btn-ghost" href="<?= e(base_url('admin/settings.php')) ?>">Профиль и контакты</a>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
