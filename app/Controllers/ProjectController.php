<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProjectController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pageTitle = 'Проекты на PHP и Bitrix';
        $metaDescription = 'Реализованные проекты на PHP, Bitrix Framework и Битрикс24 CRM — кейсы порталов, интеграций и кастомизации.';
        $canonicalPath = page_url('projects');
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Проекты'],
        ];

        $projects = db()->query(
            'SELECT id, title, slug, short_description, stack, year, role, cover_image
             FROM projects WHERE is_published = 1
             ORDER BY sort_order ASC, id DESC'
        )->fetchAll();

        $itemList = [];
        foreach ($projects as $i => $project) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => absolute_url(page_url('projects', $project['slug'])),
                'name' => $project['title'],
            ];
        }

        return View::page('projects', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'breadcrumbs' => $breadcrumbs,
            'projects' => $projects,
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

        $stmt = db()->prepare('SELECT * FROM projects WHERE slug = ? AND is_published = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $project = $stmt->fetch();
        if (!$project) {
            return $this->notFound();
        }

        $pageTitle = $project['title'];
        $metaDescription = $project['short_description'] ?: excerpt((string) $project['description'], 160);
        $canonicalPath = page_url('projects', $project['slug']);
        $ogImage = $project['cover_image'] ? upload_url($project['cover_image']) : '';
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Проекты', 'url' => page_url('projects')],
            ['name' => $project['title']],
        ];

        $creativeWork = [
            '@type' => 'CreativeWork',
            '@id' => absolute_url($canonicalPath) . '#work',
            'name' => $project['title'],
            'headline' => $project['title'],
            'description' => $metaDescription,
            'url' => absolute_url($canonicalPath),
            'inLanguage' => 'ru-RU',
            'author' => ['@id' => absolute_url() . '#person'],
            'creator' => ['@id' => absolute_url() . '#person'],
            'isPartOf' => ['@id' => absolute_url() . '#website'],
        ];
        if ($ogImage) {
            $creativeWork['image'] = absolute_url($ogImage);
        }
        if (!empty($project['year'])) {
            $creativeWork['dateCreated'] = (string) $project['year'];
        }
        if (!empty($project['stack'])) {
            $creativeWork['keywords'] = $project['stack'];
        }
        if (!empty($project['demo_url'])) {
            $creativeWork['sameAs'] = [$project['demo_url']];
        }

        $shots = db()->prepare(
            'SELECT * FROM project_screenshots WHERE project_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $shots->execute([(int) $project['id']]);

        return View::page('project', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'breadcrumbs' => $breadcrumbs,
            'project' => $project,
            'screenshots' => $shots->fetchAll(),
            'schemaGraphs' => [
                schema_person(),
                schema_website(),
                schema_breadcrumbs($breadcrumbs),
                $creativeWork,
            ],
        ]);
    }

    private function notFound(): ResponseInterface
    {
        return View::page('not-found', [
            'pageTitle' => 'Не найдено',
            'metaDescription' => 'Запрошенный проект не найден.',
            'robots' => 'noindex, follow',
            'canonicalPath' => page_url('projects'),
            'heading' => 'Проект не найден',
            'backUrl' => page_url('projects'),
            'backLabel' => 'К списку проектов',
            'schemaGraphs' => [schema_person(), schema_website()],
        ], 404);
    }
}
