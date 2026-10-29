<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$pageTitle = 'Грамоты и дипломы';
$current = 'certificates';
$topAction = '<a class="btn btn-primary" href="' . e(base_url('admin/certificate-edit.php')) . '">+ Добавить</a>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT image_path, file_path FROM certificates WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_upload($row['image_path']);
            delete_upload($row['file_path']);
            db()->prepare('DELETE FROM certificates WHERE id = ?')->execute([$id]);
            flash('ok', 'Запись удалена.');
        }
    }
    redirect(base_url('admin/certificates.php'));
}

$items = db()->query(
    'SELECT * FROM certificates ORDER BY sort_order ASC, issued_at DESC, id DESC'
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>

<div class="panel table-responsive">
  <table class="table align-middle">
    <thead>
      <tr>
        <th></th>
        <th>Название</th>
        <th>Кем выдано</th>
        <th>Дата</th>
        <th>Статус</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td>
            <?php if ($item['image_path']): ?>
              <img class="thumb" src="<?= e(upload_url($item['image_path'])) ?>" alt="">
            <?php endif; ?>
          </td>
          <td><strong><?= e($item['title']) ?></strong></td>
          <td><?= e($item['issuer']) ?></td>
          <td><?= e(format_date($item['issued_at'])) ?></td>
          <td>
            <span class="badge-status <?= $item['is_published'] ? '' : 'off' ?>">
              <?= $item['is_published'] ? 'Опубликован' : 'Черновик' ?>
            </span>
          </td>
          <td>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-ghost btn-sm" href="<?= e(base_url('admin/certificate-edit.php?id=' . $item['id'])) ?>">Изменить</a>
              <form method="post" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Удалить грамоту?">Удалить</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <tr><td colspan="6">Добавьте грамоты и дипломы о сданных экзаменах.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
