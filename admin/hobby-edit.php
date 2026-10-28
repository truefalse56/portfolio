<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$id = (int) ($_GET['id'] ?? 0);
$item = null;
$images = [];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM hobbies WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: null;
    if (!$item) {
        flash('err', 'Запись не найдена.');
        redirect(base_url('admin/hobbies.php'));
    }
    $imgStmt = db()->prepare('SELECT * FROM hobby_images WHERE hobby_id = ? ORDER BY sort_order, id');
    $imgStmt->execute([$id]);
    $images = $imgStmt->fetchAll();
}

$pageTitle = $item ? 'Редактирование хобби' : 'Новое хобби';
$current = 'hobbies';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
        redirect(base_url('admin/hobbies.php'));
    }

    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete_image') {
        $imageId = (int) ($_POST['image_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM hobby_images WHERE id = ? AND hobby_id = ?');
        $stmt->execute([$imageId, $id]);
        $img = $stmt->fetch();
        if ($img) {
            delete_upload($img['image_path']);
            db()->prepare('DELETE FROM hobby_images WHERE id = ?')->execute([$imageId]);
            flash('ok', 'Фото удалено.');
        }
        redirect(base_url('admin/hobby-edit.php?id=' . $id));
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $short = trim((string) ($_POST['short_description'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '' || $description === '') {
        flash('err', 'Заполните название и описание.');
        redirect($id ? base_url('admin/hobby-edit.php?id=' . $id) : base_url('admin/hobby-edit.php'));
    }

    $slug = $slug === '' ? slugify($title) : slugify($slug);

    try {
        $cover = $item['cover_image'] ?? null;
        $uploaded = handle_image_upload('cover_image', 'hobbies');
        if ($uploaded) {
            if ($cover) {
                delete_upload($cover);
            }
            $cover = $uploaded;
        }

        if ($item) {
            db()->prepare(
                'UPDATE hobbies SET title=?, slug=?, short_description=?, description=?, cover_image=?, is_published=?, sort_order=? WHERE id=?'
            )->execute([$title, $slug, $short, $description, $cover, $isPublished, $sortOrder, $id]);
        } else {
            db()->prepare(
                'INSERT INTO hobbies (title, slug, short_description, description, cover_image, is_published, sort_order) VALUES (?,?,?,?,?,?,?)'
            )->execute([$title, $slug, $short, $description, $cover, $isPublished, $sortOrder]);
            $id = (int) db()->lastInsertId();
        }

        if (!empty($_FILES['gallery']['name'][0])) {
            $files = $_FILES['gallery'];
            $count = count($files['name']);
            $baseOrder = count($images);
            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $_FILES['_gallery_tmp'] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $path = handle_image_upload('_gallery_tmp', 'hobbies');
                if ($path) {
                    $caption = trim((string) ($_POST['gallery_captions'][$i] ?? ''));
                    db()->prepare(
                        'INSERT INTO hobby_images (hobby_id, image_path, caption, sort_order) VALUES (?,?,?,?)'
                    )->execute([$id, $path, $caption, $baseOrder + $i]);
                }
            }
            unset($_FILES['_gallery_tmp']);
        }

        if (!empty($_POST['existing_captions']) && is_array($_POST['existing_captions'])) {
            foreach ($_POST['existing_captions'] as $imageId => $caption) {
                db()->prepare('UPDATE hobby_images SET caption = ? WHERE id = ? AND hobby_id = ?')
                    ->execute([trim((string) $caption), (int) $imageId, $id]);
            }
        }

        if (!empty($_POST['existing_sort']) && is_array($_POST['existing_sort'])) {
            foreach ($_POST['existing_sort'] as $imageId => $order) {
                db()->prepare('UPDATE hobby_images SET sort_order = ? WHERE id = ? AND hobby_id = ?')
                    ->execute([(int) $order, (int) $imageId, $id]);
            }
        }

        flash('ok', 'Хобби сохранено.');
        redirect(base_url('admin/hobby-edit.php?id=' . $id));
    } catch (Throwable $e) {
        flash('err', $e->getMessage());
        redirect($id ? base_url('admin/hobby-edit.php?id=' . $id) : base_url('admin/hobby-edit.php'));
    }
}

require __DIR__ . '/_layout_top.php';
?>

<form class="panel" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="full">
      <label>Название *</label>
      <input type="text" name="title" required value="<?= e($item['title'] ?? '') ?>">
    </div>
    <div>
      <label>Slug (URL)</label>
      <input type="text" name="slug" value="<?= e($item['slug'] ?? '') ?>" placeholder="авто из названия">
    </div>
    <div>
      <label>Порядок сортировки</label>
      <input type="number" name="sort_order" value="<?= e((string) ($item['sort_order'] ?? 0)) ?>">
    </div>
    <div class="full">
      <label>Краткое описание</label>
      <input type="text" name="short_description" maxlength="500" value="<?= e($item['short_description'] ?? '') ?>">
    </div>
    <div class="full">
      <label>Полное описание *</label>
      <textarea name="description" required><?= e($item['description'] ?? '') ?></textarea>
      <div class="help">Можно использовать **жирный** и `код`.</div>
    </div>
    <div>
      <label>Обложка (для списка)</label>
      <input type="file" name="cover_image" accept="image/*">
      <?php if (!empty($item['cover_image'])): ?>
        <img class="thumb" src="<?= e(upload_url($item['cover_image'])) ?>" alt="" style="width:160px;height:auto;margin-top:.5rem">
      <?php endif; ?>
    </div>
    <div>
      <label class="check"><input type="checkbox" name="is_published" value="1" <?= !isset($item) || !empty($item['is_published']) ? 'checked' : '' ?>> Опубликован</label>
    </div>
  </div>

  <?php if ($item): ?>
    <h2 style="margin-top:2rem">Галерея / слайдер</h2>
    <p class="help">Загрузите несколько фото — на странице хобби они отобразятся в слайдере.</p>
    <div class="shot-admin">
      <?php foreach ($images as $img): ?>
        <div class="shot-admin-item">
          <img class="thumb" src="<?= e(upload_url($img['image_path'])) ?>" alt="">
          <div>
            <label>Подпись</label>
            <input type="text" name="existing_captions[<?= (int) $img['id'] ?>]" value="<?= e($img['caption']) ?>">
            <label>Порядок</label>
            <input type="number" name="existing_sort[<?= (int) $img['id'] ?>]" value="<?= (int) $img['sort_order'] ?>">
          </div>
          <button class="btn btn-danger" type="submit" name="action" value="delete_image"
                  onclick="this.form.image_id.value='<?= (int) $img['id'] ?>'"
                  data-confirm="Удалить фото?">Удалить</button>
        </div>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="image_id" value="">
    <label style="margin-top:1rem">Добавить фото в слайдер</label>
    <input type="file" name="gallery[]" accept="image/*" multiple>
  <?php else: ?>
    <p class="help" style="margin-top:1rem">После сохранения можно будет добавить несколько фото для слайдера.</p>
  <?php endif; ?>

  <div class="actions" style="margin-top:1.5rem">
    <button class="btn" type="submit" name="action" value="save">Сохранить</button>
    <a class="btn btn-ghost" href="<?= e(base_url('admin/hobbies.php')) ?>">Назад</a>
    <?php if ($item): ?>
      <a class="btn btn-ghost" href="<?= e(page_url('hobbies', $item['slug'])) ?>" target="_blank" rel="noopener">На сайте</a>
    <?php endif; ?>
  </div>
</form>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
