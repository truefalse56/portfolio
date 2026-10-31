<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BlogController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pageTitle = 'Блог';
        $metaDescription = 'Заметки по разработке: определения, термины и примеры кода.';
        $canonicalPath = page_url('blog');
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Блог'],
        ];

        $posts = db()->query(
            'SELECT id, title, slug, excerpt, content, cover_image, published_at, created_at
             FROM posts WHERE is_published = 1
             ORDER BY COALESCE(published_at, created_at) DESC, id DESC'
        )->fetchAll();

        $itemList = [];
        foreach ($posts as $i => $item) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => absolute_url(page_url('blog', $item['slug'])),
                'name' => $item['title'],
            ];
        }

        return View::page('blog', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'breadcrumbs' => $breadcrumbs,
            'posts' => $posts,
            'schemaGraphs' => [
                schema_person(),
                schema_website(),
                schema_breadcrumbs($breadcrumbs),
                [
                    '@type' => 'Blog',
                    '@id' => absolute_url($canonicalPath) . '#blog',
                    'url' => absolute_url($canonicalPath),
                    'name' => $pageTitle,
                    'description' => $metaDescription,
                    'inLanguage' => 'ru-RU',
                    'isPartOf' => ['@id' => absolute_url() . '#website'],
                    'blogPost' => array_map(
                        static fn (array $item): array => [
                            '@type' => 'BlogPosting',
                            'headline' => $item['title'],
                            'url' => absolute_url(page_url('blog', $item['slug'])),
                        ],
                        $posts
                    ),
                    'mainEntity' => [
                        '@type' => 'ItemList',
                        'itemListElement' => $itemList,
                    ],
                ],
            ],
        ]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug = trim((string) ($args['slug'] ?? ''));
        if ($slug === '') {
            return $this->notFound();
        }

        $stmt = db()->prepare('SELECT * FROM posts WHERE slug = ? AND is_published = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $item = $stmt->fetch();
        if (!$item) {
            return $this->notFound();
        }

        $pageTitle = $item['title'];
        $plain = article_plain_text((string) $item['content']);
        $metaDescription = $item['excerpt'] !== ''
            ? (string) $item['excerpt']
            : excerpt($plain, 160);
        $canonicalPath = page_url('blog', $item['slug']);
        $ogImage = $item['cover_image'] ? upload_url($item['cover_image']) : '';
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Блог', 'url' => page_url('blog')],
            ['name' => $item['title']],
        ];

        $publishedRaw = $item['published_at'] ?: $item['created_at'];
        $publishedTs = $publishedRaw ? strtotime((string) $publishedRaw) : false;
        $modifiedTs = !empty($item['updated_at']) ? strtotime((string) $item['updated_at']) : false;

        $articleSchema = [
            '@type' => 'BlogPosting',
            '@id' => absolute_url($canonicalPath) . '#article',
            'headline' => $item['title'],
            'name' => $item['title'],
            'description' => $metaDescription,
            'url' => absolute_url($canonicalPath),
            'mainEntityOfPage' => absolute_url($canonicalPath),
            'inLanguage' => 'ru-RU',
            'author' => ['@id' => absolute_url() . '#person'],
            'publisher' => ['@id' => absolute_url() . '#person'],
            'isPartOf' => ['@id' => absolute_url(page_url('blog')) . '#blog'],
        ];
        if ($publishedTs) {
            $articleSchema['datePublished'] = date('c', $publishedTs);
        }
        if ($modifiedTs) {
            $articleSchema['dateModified'] = date('c', $modifiedTs);
        }
        if ($ogImage) {
            $articleSchema['image'] = absolute_url($ogImage);
        }

        return View::page('post', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'breadcrumbs' => $breadcrumbs,
            'item' => $item,
            'schemaGraphs' => [
                schema_person(),
                schema_website(),
                schema_breadcrumbs($breadcrumbs),
                $articleSchema,
            ],
        ]);
    }

    private function notFound(): ResponseInterface
    {
        return View::page('not-found', [
            'pageTitle' => 'Не найдено',
            'metaDescription' => 'Статья не найдена.',
            'robots' => 'noindex, follow',
            'canonicalPath' => page_url('blog'),
            'heading' => 'Статья не найдена',
            'backUrl' => page_url('blog'),
            'backLabel' => 'К разделу «Блог»',
            'schemaGraphs' => [schema_person(), schema_website()],
        ], 404);
    }
}
