<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$pageTitle = 'Проекты';
$current = 'projects';
$topAction = '<a class="btn btn-primary" href="' . e(base_url('admin/project-edit.php')) . '">+ Добавить</a>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $shots = db()->prepare('SELECT image_path FROM project_screenshots WHERE project_id = ?');
            $shots->execute([$id]);
            foreach ($shots->fetchAll() as $shot) {
                delete_upload($shot['image_path']);
            }
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            flash('ok', 'Проект удалён.');
        }
    }
    redirect(base_url('admin/projects.php'));
}

$items = db()->query(
    'SELECT id, title, slug, year, is_published, sort_order, cover_image, updated_at
     FROM projects ORDER BY sort_order ASC, id DESC'
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>

<div class="panel table-responsive">
  <table class="table align-middle">
    <thead>
      <tr>
        <th></th>
        <th>Название</th>
        <th>Год</th>
        <th>Статус</th>
        <th>Порядок</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td>
            <?php if ($item['cover_image']): ?>
              <img class="thumb" src="<?= e(upload_url($item['cover_image'])) ?>" alt="">
            <?php endif; ?>
          </td>
          <td>
            <strong><?= e($item['title']) ?></strong><br>
            <span class="slug-muted"><?= e($item['slug']) ?></span>
          </td>
          <td><?= e($item['year']) ?></td>
          <td>
            <span class="badge-status <?= $item['is_published'] ? '' : 'off' ?>">
              <?= $item['is_published'] ? 'Опубликован' : 'Черновик' ?>
            </span>
          </td>
          <td><?= (int) $item['sort_order'] ?></td>
          <td>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-ghost btn-sm" href="<?= e(base_url('admin/project-edit.php?id=' . $item['id'])) ?>">Изменить</a>
              <form method="post" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Удалить проект безвозвратно?">Удалить</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <tr><td colspan="6">Пока нет проектов. Добавьте первый.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
