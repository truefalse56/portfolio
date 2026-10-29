<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HobbyAdminController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->delete($request);
        }

        $items = db()->query(
            'SELECT id, title, slug, is_published, sort_order, cover_image,
                    (SELECT COUNT(*) FROM hobby_images hi WHERE hi.hobby_id = hobbies.id) AS images_count
             FROM hobbies ORDER BY sort_order ASC, id DESC'
        )->fetchAll();

        return View::admin('hobbies', [
            'pageTitle' => 'Хобби',
            'current' => 'hobbies',
            'topAction' => '<a class="btn btn-primary" href="' . e(admin_url('hobbies/create')) . '">+ Добавить</a>',
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
            return View::redirect(admin_url('hobbies'));
        }

        $id = (int) ($data['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM hobbies WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $shots = db()->prepare('SELECT image_path FROM hobby_images WHERE hobby_id = ?');
            $shots->execute([$id]);
            foreach ($shots->fetchAll() as $shot) {
                delete_upload($shot['image_path']);
            }
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM hobbies WHERE id = ?')->execute([$id]);
            flash('ok', 'Запись удалена.');
        }

        return View::redirect(admin_url('hobbies'));
    }

    private function form(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $item = null;
        $images = [];

        if ($id > 0) {
            $stmt = db()->prepare('SELECT * FROM hobbies WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch() ?: null;
            if (!$item) {
                flash('err', 'Запись не найдена.');
                return View::redirect(admin_url('hobbies'));
            }
            $imgStmt = db()->prepare('SELECT * FROM hobby_images WHERE hobby_id = ? ORDER BY sort_order, id');
            $imgStmt->execute([$id]);
            $images = $imgStmt->fetchAll();
        }

        if ($request->getMethod() === 'POST') {
            return $this->save($request, $id, $item, $images);
        }

        return View::admin('hobby-edit', [
            'pageTitle' => $item ? 'Редактирование хобби' : 'Новое хобби',
            'current' => 'hobbies',
            'item' => $item,
            'images' => $images,
        ]);
    }

    private function save(ServerRequestInterface $request, int $id, ?array $item, array $images): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('hobbies'));
        }

        $action = $data['action'] ?? 'save';
        if ($action === 'delete_image' && $id > 0) {
            $imageId = (int) ($data['image_id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM hobby_images WHERE id = ? AND hobby_id = ?');
            $stmt->execute([$imageId, $id]);
            $img = $stmt->fetch();
            if ($img) {
                delete_upload($img['image_path']);
                db()->prepare('DELETE FROM hobby_images WHERE id = ?')->execute([$imageId]);
                flash('ok', 'Фото удалено.');
            }
            return View::redirect(admin_url('hobbies/' . $id . '/edit'));
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $short = trim((string) ($data['short_description'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isPublished = isset($data['is_published']) ? 1 : 0;

        if ($title === '' || $description === '') {
            flash('err', 'Заполните название и описание.');
            return View::redirect($id ? admin_url('hobbies/' . $id . '/edit') : admin_url('hobbies/create'));
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
                        $caption = trim((string) ($data['gallery_captions'][$i] ?? ''));
                        db()->prepare(
                            'INSERT INTO hobby_images (hobby_id, image_path, caption, sort_order) VALUES (?,?,?,?)'
                        )->execute([$id, $path, $caption, $baseOrder + $i]);
                    }
                }
                unset($_FILES['_gallery_tmp']);
            }

            if (!empty($data['existing_captions']) && is_array($data['existing_captions'])) {
                foreach ($data['existing_captions'] as $imageId => $caption) {
                    db()->prepare('UPDATE hobby_images SET caption = ? WHERE id = ? AND hobby_id = ?')
                        ->execute([trim((string) $caption), (int) $imageId, $id]);
                }
            }

            if (!empty($data['existing_sort']) && is_array($data['existing_sort'])) {
                foreach ($data['existing_sort'] as $imageId => $order) {
                    db()->prepare('UPDATE hobby_images SET sort_order = ? WHERE id = ? AND hobby_id = ?')
                        ->execute([(int) $order, (int) $imageId, $id]);
                }
            }

            flash('ok', 'Хобби сохранено.');
            return View::redirect(admin_url('hobbies/' . $id . '/edit'));
        } catch (\Throwable $e) {
            flash('err', $e->getMessage());
            return View::redirect($id ? admin_url('hobbies/' . $id . '/edit') : admin_url('hobbies/create'));
        }
    }
}
