<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$pageTitle = 'Хобби';
$current = 'hobbies';
$topAction = '<a class="btn btn-primary" href="' . e(base_url('admin/hobby-edit.php')) . '">+ Добавить</a>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM hobbies WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $shots = db()->prepare('SELECT image_path FROM hobby_images WHERE hobby_id = ?');
            $shots->execute([$id]);
            foreach ($shots->fetchAll() as $shot) {
                delete_upload($shot['image_path']);
            }
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM hobbies WHERE id = ?')->execute([$id]);
            flash('ok', 'Запись удалена.');
        }
    }
    redirect(base_url('admin/hobbies.php'));
}

$items = db()->query(
    'SELECT id, title, slug, is_published, sort_order, cover_image,
            (SELECT COUNT(*) FROM hobby_images hi WHERE hi.hobby_id = hobbies.id) AS images_count
     FROM hobbies ORDER BY sort_order ASC, id DESC'
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>

<div class="panel table-responsive">
  <table class="table align-middle">
    <thead>
      <tr>
        <th></th>
        <th>Название</th>
        <th>Фото</th>
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
          <td><?= (int) $item['images_count'] ?></td>
          <td>
            <span class="badge-status <?= $item['is_published'] ? '' : 'off' ?>">
              <?= $item['is_published'] ? 'Опубликован' : 'Черновик' ?>
            </span>
          </td>
          <td><?= (int) $item['sort_order'] ?></td>
          <td>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-ghost btn-sm" href="<?= e(base_url('admin/hobby-edit.php?id=' . $item['id'])) ?>">Изменить</a>
              <form method="post" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Удалить запись?">Удалить</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <tr><td colspan="6">Пока нет записей. Добавьте первое хобби.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
