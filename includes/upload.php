<?php

declare(strict_types=1);

function handle_image_upload(string $field, string $subdir = ''): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Ошибка загрузки файла (код ' . $file['error'] . ').');
    }

    $app = require base_path('config/app.php');
    $maxBytes = ((int) $app['upload_max_mb']) * 1024 * 1024;

    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Файл слишком большой. Максимум ' . $app['upload_max_mb'] . ' МБ.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!isset($allowedMimes[$mime])) {
        throw new RuntimeException('Допустимы только JPG, PNG, WEBP, GIF.');
    }

    $ext = $allowedMimes[$mime];
    $subdir = trim($subdir, '/\\');
    $uploadRoot = base_path('assets/uploads' . ($subdir ? '/' . $subdir : ''));

    if (!is_dir($uploadRoot) && !mkdir($uploadRoot, 0755, true) && !is_dir($uploadRoot)) {
        throw new RuntimeException('Не удалось создать папку загрузок.');
    }

    $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadRoot . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }

    return ($subdir ? $subdir . '/' : '') . $filename;
}

function delete_upload(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $full = base_path('assets/uploads/' . ltrim($relativePath, '/\\'));
    if (is_file($full)) {
        @unlink($full);
    }
}
