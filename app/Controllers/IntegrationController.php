<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IntegrationController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pageTitle = 'Интеграции Битрикс24 CRM';
        $metaDescription = 'Интеграции и кастомизация Битрикс24 CRM: события, REST, placements, валидация полей и примеры кода на PHP.';
        $canonicalPath = page_url('integrations');
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Интеграции'],
        ];

        $integrations = db()->query(
            'SELECT id, title, slug, category, short_description, cover_image
             FROM integrations WHERE is_published = 1
             ORDER BY sort_order ASC, id DESC'
        )->fetchAll();

        $itemList = [];
        foreach ($integrations as $i => $item) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => absolute_url(page_url('integrations', $item['slug'])),
                'name' => $item['title'],
            ];
        }

        return View::page('integrations', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'breadcrumbs' => $breadcrumbs,
            'integrations' => $integrations,
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

        $stmt = db()->prepare('SELECT * FROM integrations WHERE slug = ? AND is_published = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $item = $stmt->fetch();
        if (!$item) {
            return $this->notFound();
        }

        $pageTitle = $item['title'];
        $metaDescription = $item['short_description'] ?: excerpt((string) $item['description'], 160);
        $canonicalPath = page_url('integrations', $item['slug']);
        $ogImage = $item['cover_image'] ? upload_url($item['cover_image']) : '';
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Интеграции', 'url' => page_url('integrations')],
            ['name' => $item['title']],
        ];

        $techArticle = [
            '@type' => 'TechArticle',
            '@id' => absolute_url($canonicalPath) . '#article',
            'headline' => $item['title'],
            'name' => $item['title'],
            'description' => $metaDescription,
            'url' => absolute_url($canonicalPath),
            'inLanguage' => 'ru-RU',
            'author' => ['@id' => absolute_url() . '#person'],
            'creator' => ['@id' => absolute_url() . '#person'],
            'isPartOf' => ['@id' => absolute_url() . '#website'],
            'about' => $item['category'] ?: 'Битрикс24 CRM',
        ];
        if ($ogImage) {
            $techArticle['image'] = absolute_url($ogImage);
        }

        $features = [];
        if (!empty($item['features'])) {
            $decoded = json_decode($item['features'], true);
            if (is_array($decoded)) {
                $features = $decoded;
            }
        }

        $shots = db()->prepare(
            'SELECT * FROM integration_screenshots WHERE integration_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $shots->execute([(int) $item['id']]);

        $lang = preg_replace('/[^a-z0-9+-]/i', '', $item['code_language'] ?: 'php') ?: 'php';

        return View::page('integration', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'breadcrumbs' => $breadcrumbs,
            'item' => $item,
            'features' => $features,
            'screenshots' => $shots->fetchAll(),
            'lang' => $lang,
            'schemaGraphs' => [
                schema_person(),
                schema_website(),
                schema_breadcrumbs($breadcrumbs),
                $techArticle,
            ],
        ]);
    }

    private function notFound(): ResponseInterface
    {
        return View::page('not-found', [
            'pageTitle' => 'Не найдено',
            'metaDescription' => 'Запрошенная интеграция не найдена.',
            'robots' => 'noindex, follow',
            'canonicalPath' => page_url('integrations'),
            'heading' => 'Интеграция не найдена',
            'backUrl' => page_url('integrations'),
            'backLabel' => 'К списку интеграций',
            'schemaGraphs' => [schema_person(), schema_website()],
        ], 404);
    }
}
