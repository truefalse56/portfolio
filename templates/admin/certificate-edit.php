<form class="panel" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Название *</label>
      <input class="form-control" type="text" name="title" required value="<?= e($item['title'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Кем выдано</label>
      <input class="form-control" type="text" name="issuer" value="<?= e($item['issuer'] ?? '') ?>" placeholder="1С-Битрикс">
    </div>
    <div class="col-md-6">
      <label class="form-label">Дата</label>
      <input class="form-control" type="date" name="issued_at" value="<?= e($item['issued_at'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Описание</label>
      <textarea class="form-control" name="description"><?= e($item['description'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">Изображение / скан (превью)</label>
      <input class="form-control" type="file" name="image_path" accept="image/*">
      <?php if (!empty($item['image_path'])): ?>
        <img class="thumb mt-2" src="<?= e(upload_url($item['image_path'])) ?>" alt="" style="width:180px;height:auto">
      <?php endif; ?>
    </div>
    <div class="col-md-6">
      <label class="form-label">Файл для скачивания (PDF/изображение)</label>
      <input class="form-control" type="file" name="file_path" accept=".pdf,image/*">
      <?php if (!empty($item['file_path'])): ?>
        <div class="form-text"><a href="<?= e(upload_url($item['file_path'])) ?>" target="_blank">Текущий файл</a></div>
      <?php endif; ?>
    </div>
    <div class="col-md-6">
      <label class="form-label">Порядок</label>
      <input class="form-control" type="number" name="sort_order" value="<?= e((string) ($item['sort_order'] ?? 0)) ?>">
    </div>
    <div class="col-md-6 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= !isset($item) || !empty($item['is_published']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_published">Опубликован</label>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Сохранить</button>
    <a class="btn btn-ghost" href="<?= e(admin_url('certificates')) ?>">Назад</a>
  </div>
</form>
