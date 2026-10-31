<div class="panel table-responsive">
  <table class="table align-middle">
    <thead>
      <tr>
        <th></th>
        <th>Название</th>
        <th>Дата</th>
        <th>Статус</th>
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
          <td><?= e(post_date($item)) ?></td>
          <td>
            <span class="badge-status <?= $item['is_published'] ? '' : 'off' ?>">
              <?= $item['is_published'] ? 'Опубликована' : 'Черновик' ?>
            </span>
          </td>
          <td>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-ghost btn-sm" href="<?= e(admin_url('blog/' . $item['id'] . '/edit')) ?>">Изменить</a>
              <form method="post" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Удалить статью?">Удалить</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <tr><td colspan="5">Пока нет статей. Добавьте первую заметку.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
