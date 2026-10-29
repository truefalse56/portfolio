<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CertificateAdminController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->delete($request);
        }

        $items = db()->query(
            'SELECT * FROM certificates ORDER BY sort_order ASC, issued_at DESC, id DESC'
        )->fetchAll();

        return View::admin('certificates', [
            'pageTitle' => 'Грамоты и дипломы',
            'current' => 'certificates',
            'topAction' => '<a class="btn btn-primary" href="' . e(admin_url('certificates/create')) . '">+ Добавить</a>',
            'items' => $items,
        ]);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->form($request, 0);
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->form($request, (int) ($args['id'] ?? 0));
    }

    private function delete(ServerRequestInterface $request): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('certificates'));
        }

        $id = (int) ($data['id'] ?? 0);
        $stmt = db()->prepare('SELECT image_path, file_path FROM certificates WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_upload($row['image_path']);
            delete_upload($row['file_path']);
            db()->prepare('DELETE FROM certificates WHERE id = ?')->execute([$id]);
            flash('ok', 'Запись удалена.');
        }

        return View::redirect(admin_url('certificates'));
    }

    private function form(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $item = null;
        if ($id > 0) {
            $stmt = db()->prepare('SELECT * FROM certificates WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch() ?: null;
            if (!$item) {
                flash('err', 'Запись не найдена.');
                return View::redirect(admin_url('certificates'));
            }
        }

        if ($request->getMethod() === 'POST') {
            return $this->save($request, $id, $item);
        }

        return View::admin('certificate-edit', [
            'pageTitle' => $item ? 'Редактирование сертификата' : 'Новый сертификат',
            'current' => 'certificates',
            'item' => $item,
        ]);
    }

    private function save(ServerRequestInterface $request, int $id, ?array $item): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('certificates'));
        }

        $title = trim((string) ($data['title'] ?? ''));
        $issuer = trim((string) ($data['issuer'] ?? ''));
        $issuedAt = trim((string) ($data['issued_at'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isPublished = isset($data['is_published']) ? 1 : 0;

        if ($title === '') {
            flash('err', 'Укажите название.');
            return View::redirect($id ? admin_url('certificates/' . $id . '/edit') : admin_url('certificates/create'));
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

            if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] !== UPLOAD_ERR_NO_FILE) {
                $f = $_FILES['file_path'];
                if ($f['error'] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('Ошибка загрузки файла сертификата.');
                }
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($f['tmp_name']);
                $map = [
                    'application/pdf' => 'pdf',
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];
                if (!isset($map[$mime])) {
                    throw new \RuntimeException('Файл сертификата: только PDF, JPG, PNG, WEBP.');
                }
                $dir = base_path('assets/uploads/certificates');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
                if (!move_uploaded_file($f['tmp_name'], $dir . DIRECTORY_SEPARATOR . $name)) {
                    throw new \RuntimeException('Не удалось сохранить файл.');
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
            return View::redirect(admin_url('certificates/' . $id . '/edit'));
        } catch (\Throwable $e) {
            flash('err', $e->getMessage());
            return View::redirect($id ? admin_url('certificates/' . $id . '/edit') : admin_url('certificates/create'));
        }
    }
}
