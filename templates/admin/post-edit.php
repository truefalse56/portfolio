<?php
$langs = [
    'php' => 'PHP',
    'javascript' => 'JavaScript',
    'typescript' => 'TypeScript',
    'html' => 'HTML',
    'css' => 'CSS',
    'sql' => 'SQL',
    'bash' => 'Bash',
    'json' => 'JSON',
    'python' => 'Python',
];
$publishedValue = !empty($item['published_at'])
    ? date('Y-m-d\TH:i', strtotime((string) $item['published_at']) ?: time())
    : date('Y-m-d\TH:i');
?>
<form class="panel" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Название *</label>
      <input class="form-control" type="text" name="title" required maxlength="200" value="<?= e($item['title'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Slug (URL)</label>
      <input class="form-control" type="text" name="slug" value="<?= e($item['slug'] ?? '') ?>" placeholder="авто из названия">
    </div>
    <div class="col-md-6">
      <label class="form-label">Дата публикации</label>
      <input class="form-control" type="datetime-local" name="published_at" value="<?= e($publishedValue) ?>">
      <div class="form-text">По этой дате статьи сортируются в блоге: новые сверху.</div>
    </div>
    <div class="col-12">
      <label class="form-label">Краткое описание</label>
      <textarea class="form-control" name="excerpt" maxlength="500" rows="2"><?= e($item['excerpt'] ?? '') ?></textarea>
      <div class="form-text">Показывается в списке и в превью. Если оставить пустым, соберётся из текста.</div>
    </div>
    <div class="col-12">
      <label class="form-label">Текст статьи *</label>
      <div class="d-flex flex-wrap gap-2 mb-2">
        <?php foreach ($langs as $code => $label): ?>
          <button class="btn btn-ghost btn-sm" type="button" data-insert-lang="<?= e($code) ?>"><?= e($label) ?></button>
        <?php endforeach; ?>
      </div>
      <textarea class="form-control code article" name="content" data-article-content required><?= e($item['content'] ?? '') ?></textarea>
      <div class="form-text mt-2">
        Абзацы разделяйте пустой строкой. В тексте: <strong>**жирный**</strong> и <strong>`термин`</strong>.
        Заголовки: <strong>## Раздел</strong> и <strong>### Подраздел</strong>. Список: строки с <strong>- пункт</strong>.
        Ссылка: <strong>[текст](https://example.com)</strong>.
        Кнопки языков вставляют блок кода в позицию курсора. Внутри кода не используйте три обратные кавычки подряд.
      </div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Обложка</label>
      <input class="form-control" type="file" name="cover_image" accept="image/*">
      <?php if (!empty($item['cover_image'])): ?>
        <img class="thumb mt-2" src="<?= e(upload_url($item['cover_image'])) ?>" alt="" style="width:160px;height:auto">
      <?php endif; ?>
    </div>
    <div class="col-md-6 d-flex align-items-end">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= !isset($item) || !empty($item['is_published']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_published">Опубликована</label>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Сохранить</button>
    <a class="btn btn-ghost" href="<?= e(admin_url('blog')) ?>">Назад</a>
    <?php if ($item && !empty($item['is_published'])): ?>
      <a class="btn btn-ghost" href="<?= e(page_url('blog', $item['slug'])) ?>" target="_blank" rel="noopener">На сайте</a>
    <?php endif; ?>
  </div>
</form>
