<form class="panel" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Название *</label>
      <input class="form-control" type="text" name="title" required value="<?= e($item['title'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Slug</label>
      <input class="form-control" type="text" name="slug" value="<?= e($item['slug'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Категория</label>
      <input class="form-control" type="text" name="category" value="<?= e($item['category'] ?? 'Битрикс24 CRM') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Краткое описание</label>
      <input class="form-control" type="text" name="short_description" maxlength="500" value="<?= e($item['short_description'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Полное описание *</label>
      <textarea class="form-control" name="description" required><?= e($item['description'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
      <label class="form-label">Что сделано (по одному пункту на строку)</label>
      <textarea class="form-control" name="features"><?= e($featuresText) ?></textarea>
    </div>
    <div class="col-12">
      <label class="form-label">Пример кода</label>
      <textarea class="form-control code" name="code_example"><?= e($item['code_example'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">Язык кода</label>
      <input class="form-control" type="text" name="code_language" value="<?= e($item['code_language'] ?? 'php') ?>" placeholder="php">
    </div>
    <div class="col-md-6">
      <label class="form-label">Порядок</label>
      <input class="form-control" type="number" name="sort_order" value="<?= e((string) ($item['sort_order'] ?? 0)) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Обложка / скрин</label>
      <input class="form-control" type="file" name="cover_image" accept="image/*">
      <?php if (!empty($item['cover_image'])): ?>
        <img class="thumb mt-2" src="<?= e(upload_url($item['cover_image'])) ?>" alt="" style="width:160px;height:auto">
      <?php endif; ?>
    </div>
    <div class="col-md-6 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= !isset($item) || !empty($item['is_published']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_published">Опубликован</label>
      </div>
    </div>
  </div>

  <?php if ($item): ?>
    <h2 class="mt-4">Скриншоты</h2>
    <div class="d-grid gap-3 mt-3">
      <?php foreach ($screenshots as $shot): ?>
        <div class="shot-admin-item">
          <img class="thumb" src="<?= e(upload_url($shot['image_path'])) ?>" alt="">
          <div>
            <label class="form-label">Подпись</label>
            <input class="form-control" type="text" name="existing_captions[<?= (int) $shot['id'] ?>]" value="<?= e($shot['caption']) ?>">
          </div>
          <button class="btn btn-outline-danger btn-sm" type="submit" name="action" value="delete_shot"
                  onclick="this.form.shot_id.value='<?= (int) $shot['id'] ?>'"
                  data-confirm="Удалить скриншот?">Удалить</button>
        </div>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="shot_id" value="">
    <label class="form-label mt-3">Добавить скриншоты</label>
    <input class="form-control" type="file" name="screenshots[]" accept="image/*" multiple>
  <?php endif; ?>

  <div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit" name="action" value="save">Сохранить</button>
    <a class="btn btn-ghost" href="<?= e(admin_url('integrations')) ?>">Назад</a>
    <?php if ($item): ?>
      <a class="btn btn-ghost" href="<?= e(page_url('integrations', $item['slug'])) ?>" target="_blank" rel="noopener">На сайте</a>
    <?php endif; ?>
  </div>
</form>
