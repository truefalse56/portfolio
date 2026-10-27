<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$pageTitle = 'Интеграции';
$current = 'integrations';
$topAction = '<a class="btn" href="' . e(base_url('admin/integration-edit.php')) . '">+ Добавить</a>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM integrations WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $shots = db()->prepare('SELECT image_path FROM integration_screenshots WHERE integration_id = ?');
            $shots->execute([$id]);
            foreach ($shots->fetchAll() as $shot) {
                delete_upload($shot['image_path']);
            }
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM integrations WHERE id = ?')->execute([$id]);
            flash('ok', 'Интеграция удалена.');
        }
    }
    redirect(base_url('admin/integrations.php'));
}

$items = db()->query(
    'SELECT id, title, slug, category, is_published, sort_order, cover_image
     FROM integrations ORDER BY sort_order ASC, id DESC'
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>

<div class="panel">
  <table>
    <thead>
      <tr>
        <th></th>
        <th>Название</th>
        <th>Категория</th>
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
            <span style="color:var(--muted);font-size:.8rem"><?= e($item['slug']) ?></span>
          </td>
          <td><?= e($item['category']) ?></td>
          <td>
            <span class="badge <?= $item['is_published'] ? '' : 'off' ?>">
              <?= $item['is_published'] ? 'Опубликован' : 'Черновик' ?>
            </span>
          </td>
          <td><?= (int) $item['sort_order'] ?></td>
          <td>
            <div class="actions">
              <a class="btn btn-ghost" href="<?= e(base_url('admin/integration-edit.php?id=' . $item['id'])) ?>">Изменить</a>
              <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="btn btn-danger" type="submit" data-confirm="Удалить интеграцию?">Удалить</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <tr><td colspan="6">Интеграций пока нет.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
