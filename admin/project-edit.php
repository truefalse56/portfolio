<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$id = (int) ($_GET['id'] ?? 0);
$item = null;
$screenshots = [];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: null;
    if (!$item) {
        flash('err', 'Проект не найден.');
        redirect(base_url('admin/projects.php'));
    }
    $shots = db()->prepare('SELECT * FROM project_screenshots WHERE project_id = ? ORDER BY sort_order, id');
    $shots->execute([$id]);
    $screenshots = $shots->fetchAll();
}

$pageTitle = $item ? 'Редактирование проекта' : 'Новый проект';
$current = 'projects';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
        redirect(base_url('admin/projects.php'));
    }

    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete_shot') {
        $shotId = (int) ($_POST['shot_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM project_screenshots WHERE id = ? AND project_id = ?');
        $stmt->execute([$shotId, $id]);
        $shot = $stmt->fetch();
        if ($shot) {
            delete_upload($shot['image_path']);
            db()->prepare('DELETE FROM project_screenshots WHERE id = ?')->execute([$shotId]);
            flash('ok', 'Скриншот удалён.');
        }
        redirect(base_url('admin/project-edit.php?id=' . $id));
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $short = trim((string) ($_POST['short_description'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $stack = trim((string) ($_POST['stack'] ?? ''));
    $role = trim((string) ($_POST['role'] ?? ''));
    $year = trim((string) ($_POST['year'] ?? ''));
    $demoUrl = trim((string) ($_POST['demo_url'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '' || $description === '') {
        flash('err', 'Заполните название и описание.');
        redirect($id ? base_url('admin/project-edit.php?id=' . $id) : base_url('admin/project-edit.php'));
    }

    if ($slug === '') {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }

    try {
        $cover = $item['cover_image'] ?? null;
        $uploaded = handle_image_upload('cover_image', 'projects');
        if ($uploaded) {
            if ($cover) {
                delete_upload($cover);
            }
            $cover = $uploaded;
        }

        if ($item) {
            $sql = 'UPDATE projects SET title=?, slug=?, short_description=?, description=?, stack=?, role=?, year=?, cover_image=?, demo_url=?, is_published=?, sort_order=? WHERE id=?';
            db()->prepare($sql)->execute([
                $title, $slug, $short, $description, $stack, $role, $year, $cover, $demoUrl ?: null, $isPublished, $sortOrder, $id,
            ]);
        } else {
            $sql = 'INSERT INTO projects (title, slug, short_description, description, stack, role, year, cover_image, demo_url, is_published, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
            db()->prepare($sql)->execute([
                $title, $slug, $short, $description, $stack, $role, $year, $cover, $demoUrl ?: null, $isPublished, $sortOrder,
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
                $path = handle_image_upload('_shot_tmp', 'projects');
                if ($path) {
                    $caption = trim((string) ($_POST['screenshot_captions'][$i] ?? ''));
                    db()->prepare(
                        'INSERT INTO project_screenshots (project_id, image_path, caption, sort_order) VALUES (?,?,?,?)'
                    )->execute([$id, $path, $caption, $i]);
                }
            }
            unset($_FILES['_shot_tmp']);
        }

        if (!empty($_POST['existing_captions']) && is_array($_POST['existing_captions'])) {
            foreach ($_POST['existing_captions'] as $shotId => $caption) {
                db()->prepare('UPDATE project_screenshots SET caption = ? WHERE id = ? AND project_id = ?')
                    ->execute([trim((string) $caption), (int) $shotId, $id]);
            }
        }

        flash('ok', 'Проект сохранён.');
        redirect(base_url('admin/project-edit.php?id=' . $id));
    } catch (Throwable $e) {
        flash('err', $e->getMessage());
        redirect($id ? base_url('admin/project-edit.php?id=' . $id) : base_url('admin/project-edit.php'));
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
      <label class="form-label">Slug (URL)</label>
      <input class="form-control" type="text" name="slug" value="<?= e($item['slug'] ?? '') ?>" placeholder="авто из названия">
    </div>
    <div class="col-md-6">
      <label class="form-label">Год</label>
      <input class="form-control" type="text" name="year" value="<?= e($item['year'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Роль</label>
      <input class="form-control" type="text" name="role" value="<?= e($item['role'] ?? '') ?>" placeholder="Fullstack-разработчик">
    </div>
    <div class="col-md-6">
      <label class="form-label">Стек</label>
      <input class="form-control" type="text" name="stack" value="<?= e($item['stack'] ?? '') ?>" placeholder="PHP, Bitrix, MySQL">
    </div>
    <div class="col-12">
      <label class="form-label">Краткое описание</label>
      <input class="form-control" type="text" name="short_description" maxlength="500" value="<?= e($item['short_description'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Полное описание *</label>
      <textarea class="form-control" name="description" required><?= e($item['description'] ?? '') ?></textarea>
      <div class="form-text">Можно использовать **жирный** и `код`.</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Ссылка на демо</label>
      <input class="form-control" type="url" name="demo_url" value="<?= e($item['demo_url'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Порядок сортировки</label>
      <input class="form-control" type="number" name="sort_order" value="<?= e((string) ($item['sort_order'] ?? 0)) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Обложка</label>
      <input class="form-control" type="file" name="cover_image" accept="image/*">
      <?php if (!empty($item['cover_image'])): ?>
        <div class="form-text">Текущая: <a href="<?= e(upload_url($item['cover_image'])) ?>" target="_blank">открыть</a></div>
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
  <?php else: ?>
    <p class="form-text mt-3">После сохранения можно будет добавить скриншоты.</p>
  <?php endif; ?>

  <div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-primary" type="submit" name="action" value="save">Сохранить</button>
    <a class="btn btn-ghost" href="<?= e(base_url('admin/projects.php')) ?>">Назад</a>
    <?php if ($item): ?>
      <a class="btn btn-ghost" href="<?= e(page_url('projects', $item['slug'])) ?>" target="_blank" rel="noopener">На сайте</a>
    <?php endif; ?>
  </div>
</form>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
