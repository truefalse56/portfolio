<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CertificateController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pageTitle = 'Сертификаты';
        $metaDescription = 'Сертификаты и документы о сдаче экзаменов — подтверждённые компетенции в веб-разработке и Bitrix.';
        $canonicalPath = page_url('certificates');
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => page_url('home')],
            ['name' => 'Сертификаты'],
        ];

        $certificates = db()->query(
            'SELECT * FROM certificates WHERE is_published = 1
             ORDER BY sort_order ASC, issued_at DESC, id DESC'
        )->fetchAll();

        $itemList = [];
        foreach ($certificates as $i => $cert) {
            $entry = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $cert['title'],
            ];
            if (!empty($cert['image_path'])) {
                $entry['url'] = absolute_url(upload_url($cert['image_path']));
            }
            $itemList[] = $entry;
        }

        return View::page('certificates', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalPath' => $canonicalPath,
            'breadcrumbs' => $breadcrumbs,
            'certificates' => $certificates,
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
}
