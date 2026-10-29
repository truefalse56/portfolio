<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProjectAdminController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->delete($request);
        }

        $items = db()->query(
            'SELECT id, title, slug, year, is_published, sort_order, cover_image, updated_at
             FROM projects ORDER BY sort_order ASC, id DESC'
        )->fetchAll();

        return View::admin('projects', [
            'pageTitle' => 'Проекты',
            'current' => 'projects',
            'topAction' => '<a class="btn btn-primary" href="' . e(admin_url('projects/create')) . '">+ Добавить</a>',
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
            return View::redirect(admin_url('projects'));
        }

        $id = (int) ($data['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $shots = db()->prepare('SELECT image_path FROM project_screenshots WHERE project_id = ?');
            $shots->execute([$id]);
            foreach ($shots->fetchAll() as $shot) {
                delete_upload($shot['image_path']);
            }
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            flash('ok', 'Проект удалён.');
        }

        return View::redirect(admin_url('projects'));
    }

    private function form(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $item = null;
        $screenshots = [];

        if ($id > 0) {
            $stmt = db()->prepare('SELECT * FROM projects WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch() ?: null;
            if (!$item) {
                flash('err', 'Проект не найден.');
                return View::redirect(admin_url('projects'));
            }
            $shots = db()->prepare('SELECT * FROM project_screenshots WHERE project_id = ? ORDER BY sort_order, id');
            $shots->execute([$id]);
            $screenshots = $shots->fetchAll();
        }

        if ($request->getMethod() === 'POST') {
            return $this->save($request, $id, $item, $screenshots);
        }

        return View::admin('project-edit', [
            'pageTitle' => $item ? 'Редактирование проекта' : 'Новый проект',
            'current' => 'projects',
            'item' => $item,
            'screenshots' => $screenshots,
        ]);
    }

    private function save(ServerRequestInterface $request, int $id, ?array $item, array $screenshots): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('projects'));
        }

        $action = $data['action'] ?? 'save';

        if ($action === 'delete_shot' && $id > 0) {
            $shotId = (int) ($data['shot_id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM project_screenshots WHERE id = ? AND project_id = ?');
            $stmt->execute([$shotId, $id]);
            $shot = $stmt->fetch();
            if ($shot) {
                delete_upload($shot['image_path']);
                db()->prepare('DELETE FROM project_screenshots WHERE id = ?')->execute([$shotId]);
                flash('ok', 'Скриншот удалён.');
            }
            return View::redirect(admin_url('projects/' . $id . '/edit'));
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $short = trim((string) ($data['short_description'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $stack = trim((string) ($data['stack'] ?? ''));
        $role = trim((string) ($data['role'] ?? ''));
        $year = trim((string) ($data['year'] ?? ''));
        $demoUrl = trim((string) ($data['demo_url'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isPublished = isset($data['is_published']) ? 1 : 0;

        if ($title === '' || $description === '') {
            flash('err', 'Заполните название и описание.');
            return View::redirect($id ? admin_url('projects/' . $id . '/edit') : admin_url('projects/create'));
        }

        $slug = $slug === '' ? slugify($title) : slugify($slug);

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
                db()->prepare(
                    'UPDATE projects SET title=?, slug=?, short_description=?, description=?, stack=?, role=?, year=?, cover_image=?, demo_url=?, is_published=?, sort_order=? WHERE id=?'
                )->execute([
                    $title, $slug, $short, $description, $stack, $role, $year, $cover, $demoUrl ?: null, $isPublished, $sortOrder, $id,
                ]);
            } else {
                db()->prepare(
                    'INSERT INTO projects (title, slug, short_description, description, stack, role, year, cover_image, demo_url, is_published, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([
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
                        $caption = trim((string) ($data['screenshot_captions'][$i] ?? ''));
                        db()->prepare(
                            'INSERT INTO project_screenshots (project_id, image_path, caption, sort_order) VALUES (?,?,?,?)'
                        )->execute([$id, $path, $caption, $i]);
                    }
                }
                unset($_FILES['_shot_tmp']);
            }

            if (!empty($data['existing_captions']) && is_array($data['existing_captions'])) {
                foreach ($data['existing_captions'] as $shotId => $caption) {
                    db()->prepare('UPDATE project_screenshots SET caption = ? WHERE id = ? AND project_id = ?')
                        ->execute([trim((string) $caption), (int) $shotId, $id]);
                }
            }

            flash('ok', 'Проект сохранён.');
            return View::redirect(admin_url('projects/' . $id . '/edit'));
        } catch (\Throwable $e) {
            flash('err', $e->getMessage());
            return View::redirect($id ? admin_url('projects/' . $id . '/edit') : admin_url('projects/create'));
        }
    }
}
