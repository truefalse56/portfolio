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
              <a class="btn btn-ghost btn-sm" href="<?= e(admin_url('certificates/' . $item['id'] . '/edit')) ?>">Изменить</a>
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
