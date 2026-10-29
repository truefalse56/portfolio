<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IntegrationAdminController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->delete($request);
        }

        $items = db()->query(
            'SELECT id, title, slug, category, is_published, sort_order, cover_image
             FROM integrations ORDER BY sort_order ASC, id DESC'
        )->fetchAll();

        return View::admin('integrations', [
            'pageTitle' => 'Интеграции',
            'current' => 'integrations',
            'topAction' => '<a class="btn btn-primary" href="' . e(admin_url('integrations/create')) . '">+ Добавить</a>',
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
            return View::redirect(admin_url('integrations'));
        }

        $id = (int) ($data['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM integrations WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $shots = db()->prepare('SELECT image_path FROM integration_screenshots WHERE integration_id = ?');
            $shots->execute([$id]);
            foreach ($shots->fetchAll() as $shot) {
                delete_upload($shot['image_path']);
            }
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM integrations WHERE id = ?')->execute([$id]);
            flash('ok', 'Интеграция удалена.');
        }

        return View::redirect(admin_url('integrations'));
    }

    private function form(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $item = null;
        $screenshots = [];

        if ($id > 0) {
            $stmt = db()->prepare('SELECT * FROM integrations WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch() ?: null;
            if (!$item) {
                flash('err', 'Интеграция не найдена.');
                return View::redirect(admin_url('integrations'));
            }
            $shots = db()->prepare('SELECT * FROM integration_screenshots WHERE integration_id = ? ORDER BY sort_order, id');
            $shots->execute([$id]);
            $screenshots = $shots->fetchAll();
        }

        if ($request->getMethod() === 'POST') {
            return $this->save($request, $id, $item);
        }

        $featuresText = '';
        if (!empty($item['features'])) {
            $decoded = json_decode($item['features'], true);
            if (is_array($decoded)) {
                $featuresText = implode("\n", $decoded);
            }
        }

        return View::admin('integration-edit', [
            'pageTitle' => $item ? 'Редактирование интеграции' : 'Новая интеграция',
            'current' => 'integrations',
            'item' => $item,
            'screenshots' => $screenshots,
            'featuresText' => $featuresText,
        ]);
    }

    private function save(ServerRequestInterface $request, int $id, ?array $item): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('integrations'));
        }

        $action = $data['action'] ?? 'save';
        if ($action === 'delete_shot' && $id > 0) {
            $shotId = (int) ($data['shot_id'] ?? 0);
            $stmt = db()->prepare('SELECT * FROM integration_screenshots WHERE id = ? AND integration_id = ?');
            $stmt->execute([$shotId, $id]);
            $shot = $stmt->fetch();
            if ($shot) {
                delete_upload($shot['image_path']);
                db()->prepare('DELETE FROM integration_screenshots WHERE id = ?')->execute([$shotId]);
                flash('ok', 'Скриншот удалён.');
            }
            return View::redirect(admin_url('integrations/' . $id . '/edit'));
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $category = trim((string) ($data['category'] ?? 'Битрикс24 CRM'));
        $short = trim((string) ($data['short_description'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $featuresRaw = trim((string) ($data['features'] ?? ''));
        $codeExample = (string) ($data['code_example'] ?? '');
        $codeLanguage = trim((string) ($data['code_language'] ?? 'php')) ?: 'php';
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isPublished = isset($data['is_published']) ? 1 : 0;

        if ($title === '' || $description === '') {
            flash('err', 'Заполните название и описание.');
            return View::redirect($id ? admin_url('integrations/' . $id . '/edit') : admin_url('integrations/create'));
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

            if (!empty($data['existing_captions']) && is_array($data['existing_captions'])) {
                foreach ($data['existing_captions'] as $shotId => $caption) {
                    db()->prepare('UPDATE integration_screenshots SET caption = ? WHERE id = ? AND integration_id = ?')
                        ->execute([trim((string) $caption), (int) $shotId, $id]);
                }
            }

            flash('ok', 'Интеграция сохранена.');
            return View::redirect(admin_url('integrations/' . $id . '/edit'));
        } catch (\Throwable $e) {
            flash('err', $e->getMessage());
            return View::redirect($id ? admin_url('integrations/' . $id . '/edit') : admin_url('integrations/create'));
        }
    }
}
