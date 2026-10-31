<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HomeController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $projects = db()->query(
            'SELECT id, title, slug, short_description, stack, year, cover_image
             FROM projects WHERE is_published = 1
             ORDER BY sort_order ASC, id DESC LIMIT 3'
        )->fetchAll();

        $integrations = db()->query(
            'SELECT id, title, slug, category, short_description, cover_image
             FROM integrations WHERE is_published = 1
             ORDER BY sort_order ASC, id DESC LIMIT 4'
        )->fetchAll();

        $hobbies = db()->query(
            'SELECT id, title, slug, short_description, cover_image
             FROM hobbies WHERE is_published = 1
             ORDER BY sort_order ASC, id DESC LIMIT 3'
        )->fetchAll();

        $posts = db()->query(
            'SELECT id, title, slug, excerpt, content, cover_image, published_at, created_at
             FROM posts WHERE is_published = 1
             ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 3'
        )->fetchAll();

        $fullName = setting('full_name', 'Килин Вячеслав Александрович');
        $title = setting('title', 'Fullstack Web Разработчик');
        $subtitle = setting('subtitle', 'PHP · Bitrix Framework · Битрикс24 CRM');
        $about = setting('about', '');
        $ctaText = setting('hero_cta_text', 'Смотреть проекты');
        $ctaLink = setting('hero_cta_link', 'projects');
        if ($ctaLink && !str_starts_with($ctaLink, 'http') && !str_starts_with($ctaLink, '#')) {
            $ctaLink = public_path($ctaLink);
        }

        $metaDescription = setting('meta_description', '')
            ?: ($fullName . ' — ' . $title . '. ' . $subtitle . '. Специализация: интеграции Битрикс24 CRM, кастомизация и PHP.');

        $data = [
            'projects' => $projects,
            'integrations' => $integrations,
            'hobbies' => $hobbies,
            'posts' => $posts,
            'fullName' => $fullName,
            'title' => $title,
            'subtitle' => $subtitle,
            'about' => $about,
            'ctaText' => $ctaText,
            'ctaLink' => $ctaLink,
            'metaDescription' => $metaDescription,
            'canonicalPath' => page_url('home'),
            'ogType' => 'website',
            'ogImage' => ($projects && !empty($projects[0]['cover_image'])) ? upload_url($projects[0]['cover_image']) : '',
            'schemaGraphs' => [
                schema_person(),
                schema_website(),
                [
                    '@type' => 'ProfilePage',
                    '@id' => absolute_url() . '#profile',
                    'url' => absolute_url(),
                    'name' => $fullName . ' — портфолио',
                    'mainEntity' => ['@id' => absolute_url() . '#person'],
                    'inLanguage' => 'ru-RU',
                ],
            ],
        ];

        return View::page('home', $data);
    }
}
