<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BlogAdminController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->delete($request);
        }

        $items = db()->query(
            'SELECT id, title, slug, is_published, published_at, created_at, cover_image
             FROM posts
             ORDER BY COALESCE(published_at, created_at) DESC, id DESC'
        )->fetchAll();

        return View::admin('posts', [
            'pageTitle' => 'Блог',
            'current' => 'blog',
            'topAction' => '<a class="btn btn-primary" href="' . e(admin_url('blog/create')) . '">+ Добавить</a>',
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
            return View::redirect(admin_url('blog'));
        }

        $id = (int) ($data['id'] ?? 0);
        $stmt = db()->prepare('SELECT cover_image FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_upload($row['cover_image']);
            db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
            flash('ok', 'Статья удалена.');
        }

        return View::redirect(admin_url('blog'));
    }

    private function form(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $item = null;

        if ($id > 0) {
            $stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch() ?: null;
            if (!$item) {
                flash('err', 'Статья не найдена.');
                return View::redirect(admin_url('blog'));
            }
        }

        if ($request->getMethod() === 'POST') {
            return $this->save($request, $id, $item);
        }

        return View::admin('post-edit', [
            'pageTitle' => $item ? 'Редактирование статьи' : 'Новая статья',
            'current' => 'blog',
            'item' => $item,
        ]);
    }

    private function save(ServerRequestInterface $request, int $id, ?array $item): ResponseInterface
    {
        $data = (array) $request->getParsedBody();
        if (!verify_csrf($data['_csrf'] ?? null)) {
            flash('err', 'Неверный CSRF-токен.');
            return View::redirect(admin_url('blog'));
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slugInput = trim((string) ($data['slug'] ?? ''));
        $excerpt = trim((string) ($data['excerpt'] ?? ''));
        $content = trim((string) ($data['content'] ?? ''));
        $isPublished = isset($data['is_published']) ? 1 : 0;
        $back = $id ? admin_url('blog/' . $id . '/edit') : admin_url('blog/create');

        if ($title === '' || $content === '') {
            flash('err', 'Заполните название и текст статьи.');
            return View::redirect($back);
        }
        if (mb_strlen($title) > 200) {
            flash('err', 'Название не длиннее 200 символов.');
            return View::redirect($back);
        }

        if ($excerpt === '') {
            $excerpt = article_excerpt($content, 240);
        }
        if (mb_strlen($excerpt) > 500) {
            $excerpt = mb_substr($excerpt, 0, 500);
        }

        $slug = $slugInput === '' ? slugify($title) : slugify($slugInput);
        if ($this->slugTaken($slug, $id)) {
            if ($slugInput === '') {
                $slug = $this->uniqueSlug($slug, $id);
            } else {
                flash('err', 'Такой адрес (slug) уже используется.');
                return View::redirect($back);
            }
        }

        $publishedRaw = trim((string) ($data['published_at'] ?? ''));
        $publishedTs = $publishedRaw !== '' ? strtotime($publishedRaw) : time();
        if ($publishedTs === false) {
            $publishedTs = time();
        }
        $publishedAt = date('Y-m-d H:i:s', $publishedTs);

        try {
            $cover = $item['cover_image'] ?? null;
            $uploaded = handle_image_upload('cover_image', 'blog');
            if ($uploaded) {
                if ($cover) {
                    delete_upload($cover);
                }
                $cover = $uploaded;
            }

            if ($item) {
                db()->prepare(
                    'UPDATE posts SET title=?, slug=?, excerpt=?, content=?, cover_image=?, published_at=?, is_published=? WHERE id=?'
                )->execute([$title, $slug, $excerpt, $content, $cover, $publishedAt, $isPublished, $id]);
            } else {
                db()->prepare(
                    'INSERT INTO posts (title, slug, excerpt, content, cover_image, published_at, is_published) VALUES (?,?,?,?,?,?,?)'
                )->execute([$title, $slug, $excerpt, $content, $cover, $publishedAt, $isPublished]);
                $id = (int) db()->lastInsertId();
            }

            flash('ok', 'Статья сохранена.');
            return View::redirect(admin_url('blog/' . $id . '/edit'));
        } catch (\Throwable $e) {
            flash('err', $e->getMessage());
            return View::redirect($id ? admin_url('blog/' . $id . '/edit') : admin_url('blog/create'));
        }
    }

    private function slugTaken(string $slug, int $id): bool
    {
        $stmt = db()->prepare('SELECT id FROM posts WHERE slug = ? AND id <> ? LIMIT 1');
        $stmt->execute([$slug, $id]);

        return (bool) $stmt->fetch();
    }

    private function uniqueSlug(string $slug, int $id): string
    {
        $base = $slug;
        for ($i = 2; $i <= 50; $i++) {
            $candidate = $base . '-' . $i;
            if (!$this->slugTaken($candidate, $id)) {
                return $candidate;
            }
        }

        return $base . '-' . bin2hex(random_bytes(2));
    }
}
