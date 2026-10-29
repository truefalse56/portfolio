<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HobbyController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pageTitle = 'Хобби';
        $metaDescription = 'Хобби и интересы вне разработки — фото и описания занятий автора портфолио.';
        $canonicalPath = page_url('hobbies');
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Хобби'],
        ];

        $hobbies = db()->query(
            'SELECT id, title, slug, short_description, cover_image,
                    (SELECT COUNT(*) FROM hobby_images hi WHERE hi.hobby_id = hobbies.id) AS images_count
             FROM hobbies WHERE is_published = 1
             ORDER BY sort_order ASC, id DESC'
        )->fetchAll();

        $itemList = [];
        foreach ($hobbies as $i => $item) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => absolute_url(page_url('hobbies', $item['slug'])),
                'name' => $item['title'],
            ];
        }

        return View::page('hobbies', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'breadcrumbs' => $breadcrumbs,
            'hobbies' => $hobbies,
            'schemaGraphs' => [
                schema_person(),
                schema_website(),
                schema_breadcrumbs($breadcrumbs),
                [
                    '@type' => 'CollectionPage',
                    '@id' => absolute_url($canonicalPath) . '#collection',
                    'url' => absolute_url($canonicalPath),
                    'name' => $pageTitle,
                    'description' => $metaDescription,
                    'isPartOf' => ['@id' => absolute_url() . '#website'],
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

        $stmt = db()->prepare('SELECT * FROM hobbies WHERE slug = ? AND is_published = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $item = $stmt->fetch();
        if (!$item) {
            return $this->notFound();
        }

        $pageTitle = $item['title'];
        $metaDescription = $item['short_description'] ?: excerpt((string) $item['description'], 160);
        $canonicalPath = page_url('hobbies', $item['slug']);
        $ogImage = $item['cover_image'] ? upload_url($item['cover_image']) : '';
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Хобби', 'url' => page_url('hobbies')],
            ['name' => $item['title']],
        ];

        $articleSchema = [
            '@type' => 'Article',
            '@id' => absolute_url($canonicalPath) . '#article',
            'headline' => $item['title'],
            'name' => $item['title'],
            'description' => $metaDescription,
            'url' => absolute_url($canonicalPath),
            'inLanguage' => 'ru-RU',
            'author' => ['@id' => absolute_url() . '#person'],
            'isPartOf' => ['@id' => absolute_url() . '#website'],
        ];
        if ($ogImage) {
            $articleSchema['image'] = absolute_url($ogImage);
        }

        $imagesStmt = db()->prepare(
            'SELECT * FROM hobby_images WHERE hobby_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $imagesStmt->execute([(int) $item['id']]);
        $images = $imagesStmt->fetchAll();

        $slides = $images;
        if (!$slides && $item['cover_image']) {
            $slides = [[
                'image_path' => $item['cover_image'],
                'caption' => $item['title'],
            ]];
        }

        $hasCaptions = false;
        foreach ($slides as $slide) {
            if (!empty($slide['caption'])) {
                $hasCaptions = true;
                break;
            }
        }

        return View::page('hobby', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'breadcrumbs' => $breadcrumbs,
            'item' => $item,
            'slides' => $slides,
            'hasCaptions' => $hasCaptions,
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
            'metaDescription' => 'Запись не найдена.',
            'robots' => 'noindex, follow',
            'canonicalPath' => page_url('hobbies'),
            'heading' => 'Запись не найдена',
            'backUrl' => page_url('hobbies'),
            'backLabel' => 'К разделу «Хобби»',
            'schemaGraphs' => [schema_person(), schema_website()],
        ], 404);
    }
}
