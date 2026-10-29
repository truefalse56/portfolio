<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$id = (int) ($_GET['id'] ?? 0);
$item = null;
$screenshots = [];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM integrations WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: null;
    if (!$item) {
        flash('err', 'Интеграция не найдена.');
        redirect(base_url('admin/integrations.php'));
    }
    $shots = db()->prepare('SELECT * FROM integration_screenshots WHERE integration_id = ? ORDER BY sort_order, id');
    $shots->execute([$id]);
    $screenshots = $shots->fetchAll();
}

$pageTitle = $item ? 'Редактирование интеграции' : 'Новая интеграция';
$current = 'integrations';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
        redirect(base_url('admin/integrations.php'));
    }

    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete_shot') {
        $shotId = (int) ($_POST['shot_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM integration_screenshots WHERE id = ? AND integration_id = ?');
        $stmt->execute([$shotId, $id]);
        $shot = $stmt->fetch();
        if ($shot) {
            delete_upload($shot['image_path']);
            db()->prepare('DELETE FROM integration_screenshots WHERE id = ?')->execute([$shotId]);
            flash('ok', 'Скриншот удалён.');
        }
        redirect(base_url('admin/integration-edit.php?id=' . $id));
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? 'Битрикс24 CRM'));
    $short = trim((string) ($_POST['short_description'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $featuresRaw = trim((string) ($_POST['features'] ?? ''));
    $codeExample = (string) ($_POST['code_example'] ?? '');
    $codeLanguage = trim((string) ($_POST['code_language'] ?? 'php')) ?: 'php';
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '' || $description === '') {
        flash('err', 'Заполните название и описание.');
        redirect($id ? base_url('admin/integration-edit.php?id=' . $id) : base_url('admin/integration-edit.php'));
    }

    $slug = $slug === '' ? slugify($title) : slugify($slug);

    $features = [];
    foreach (preg_split('/\r\n|\r|\n/', $featuresRaw) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '') {
            $features[] = $line;
        }
    }
    $featuresJson = $features ? json_encode($features, JSON_UNESCAPED_UNICODE) : null;

    try {
        $cover = $item['cover_image'] ?? null;
        $uploaded = handle_image_upload('cover_image', 'integrations');
        if ($uploaded) {
            if ($cover) {
                delete_upload($cover);
            }
            $cover = $uploaded;
        }

        if ($item) {
            db()->prepare(
                'UPDATE integrations SET title=?, slug=?, category=?, short_description=?, description=?, features=?, code_example=?, code_language=?, cover_image=?, is_published=?, sort_order=? WHERE id=?'
            )->execute([
                $title, $slug, $category, $short, $description, $featuresJson, $codeExample, $codeLanguage, $cover, $isPublished, $sortOrder, $id,
            ]);
        } else {
            db()->prepare(
                'INSERT INTO integrations (title, slug, category, short_description, description, features, code_example, code_language, cover_image, is_published, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $title, $slug, $category, $short, $description, $featuresJson, $codeExample, $codeLanguage, $cover, $isPublished, $sortOrder,
            ]);
            $id = (int) db()->lastInsertId();
        }

        if (!empty($_FILES['screenshots']['name'][0])) {
            $files = $_FILES['screenshots'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $_FILES['_shot_tmp'] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $path = handle_image_upload('_shot_tmp', 'integrations');
                if ($path) {
                    db()->prepare(
                        'INSERT INTO integration_screenshots (integration_id, image_path, caption, sort_order) VALUES (?,?,?,?)'
                    )->execute([$id, $path, '', $i]);
                }
            }
            unset($_FILES['_shot_tmp']);
        }

        if (!empty($_POST['existing_captions']) && is_array($_POST['existing_captions'])) {
            foreach ($_POST['existing_captions'] as $shotId => $caption) {
                db()->prepare('UPDATE integration_screenshots SET caption = ? WHERE id = ? AND integration_id = ?')
                    ->execute([trim((string) $caption), (int) $shotId, $id]);
            }
        }

        flash('ok', 'Интеграция сохранена.');
        redirect(base_url('admin/integration-edit.php?id=' . $id));
    } catch (Throwable $e) {
        flash('err', $e->getMessage());
        redirect($id ? base_url('admin/integration-edit.php?id=' . $id) : base_url('admin/integration-edit.php'));
    }
}

$featuresText = '';
if (!empty($item['features'])) {
    $decoded = json_decode($item['features'], true);
    if (is_array($decoded)) {
        $featuresText = implode("\n", $decoded);
    }
}

require __DIR__ . '/_layout_top.php';
?>

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
    <a class="btn btn-ghost" href="<?= e(base_url('admin/integrations.php')) ?>">Назад</a>
    <?php if ($item): ?>
      <a class="btn btn-ghost" href="<?= e(page_url('integrations', $item['slug'])) ?>" target="_blank" rel="noopener">На сайте</a>
    <?php endif; ?>
  </div>
</form>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
