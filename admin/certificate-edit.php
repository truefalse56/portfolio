<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require_auth();

$id = (int) ($_GET['id'] ?? 0);
$item = null;

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM certificates WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: null;
    if (!$item) {
        flash('err', 'Запись не найдена.');
        redirect(base_url('admin/certificates.php'));
    }
}

$pageTitle = $item ? 'Редактирование сертификата' : 'Новый сертификат';
$current = 'certificates';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        flash('err', 'Неверный CSRF-токен.');
        redirect(base_url('admin/certificates.php'));
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $issuer = trim((string) ($_POST['issuer'] ?? ''));
    $issuedAt = trim((string) ($_POST['issued_at'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') {
        flash('err', 'Укажите название.');
        redirect($id ? base_url('admin/certificate-edit.php?id=' . $id) : base_url('admin/certificate-edit.php'));
    }

    $issuedAtDb = $issuedAt !== '' ? $issuedAt : null;

    try {
        $image = $item['image_path'] ?? null;
        $file = $item['file_path'] ?? null;

        $uploadedImage = handle_image_upload('image_path', 'certificates');
        if ($uploadedImage) {
            if ($image) {
                delete_upload($image);
            }
            $image = $uploadedImage;
        }

        // PDF / image file as downloadable attachment
        if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['file_path'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Ошибка загрузки файла сертификата.');
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($f['tmp_name']);
            $map = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            if (!isset($map[$mime])) {
                throw new RuntimeException('Файл сертификата: только PDF, JPG, PNG, WEBP.');
            }
            $dir = base_path('assets/uploads/certificates');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
            if (!move_uploaded_file($f['tmp_name'], $dir . DIRECTORY_SEPARATOR . $name)) {
                throw new RuntimeException('Не удалось сохранить файл.');
            }
            if ($file) {
                delete_upload($file);
            }
            $file = 'certificates/' . $name;
        }

        if ($item) {
            db()->prepare(
                'UPDATE certificates SET title=?, issuer=?, issued_at=?, description=?, image_path=?, file_path=?, is_published=?, sort_order=? WHERE id=?'
            )->execute([$title, $issuer, $issuedAtDb, $description, $image, $file, $isPublished, $sortOrder, $id]);
        } else {
            db()->prepare(
                'INSERT INTO certificates (title, issuer, issued_at, description, image_path, file_path, is_published, sort_order) VALUES (?,?,?,?,?,?,?,?)'
            )->execute([$title, $issuer, $issuedAtDb, $description, $image, $file, $isPublished, $sortOrder]);
            $id = (int) db()->lastInsertId();
        }

        flash('ok', 'Грамота сохранена.');
        redirect(base_url('admin/certificate-edit.php?id=' . $id));
    } catch (Throwable $e) {
        flash('err', $e->getMessage());
        redirect($id ? base_url('admin/certificate-edit.php?id=' . $id) : base_url('admin/certificate-edit.php'));
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
    <a class="btn btn-ghost" href="<?= e(base_url('admin/certificates.php')) ?>">Назад</a>
  </div>
</form>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
