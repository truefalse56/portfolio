<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SeoController
{
    public function robots(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sitemap = absolute_url('sitemap.xml');
        $body = <<<TXT
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /config/
Disallow: /includes/
Disallow: /sql/
Disallow: /templates/
Disallow: /storage/
Disallow: /app/
Disallow: /vendor/
Disallow: /fix-database.php

Sitemap: {$sitemap}

TXT;

        $response = View::html($body, 200, 'text/plain; charset=UTF-8');
        return $response->withHeader('X-Robots-Tag', 'noindex');
    }

    public function sitemap(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $urls = [];
        $add = static function (string $loc, string $changefreq = 'weekly', string $priority = '0.7', ?string $lastmod = null) use (&$urls): void {
            $urls[] = [
                'loc' => absolute_url($loc),
                'changefreq' => $changefreq,
                'priority' => $priority,
                'lastmod' => $lastmod,
            ];
        };

        $add(page_url('home'), 'weekly', '1.0');
        $add(page_url('projects'), 'weekly', '0.9');
        $add(page_url('integrations'), 'weekly', '0.9');
        $add(page_url('hobbies'), 'monthly', '0.6');
        $add(page_url('blog'), 'weekly', '0.8');
        $add(page_url('certificates'), 'monthly', '0.7');

        try {
            foreach (db()->query('SELECT slug, updated_at, created_at FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, id DESC')->fetchAll() as $row) {
                $mod = $row['updated_at'] ?? $row['created_at'] ?? null;
                $add(page_url('projects', $row['slug']), 'monthly', '0.8', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
            }
            foreach (db()->query('SELECT slug, updated_at, created_at FROM integrations WHERE is_published = 1 ORDER BY sort_order ASC, id DESC')->fetchAll() as $row) {
                $mod = $row['updated_at'] ?? $row['created_at'] ?? null;
                $add(page_url('integrations', $row['slug']), 'monthly', '0.8', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
            }
            foreach (db()->query('SELECT slug, updated_at, created_at FROM hobbies WHERE is_published = 1 ORDER BY sort_order ASC, id DESC')->fetchAll() as $row) {
                $mod = $row['updated_at'] ?? $row['created_at'] ?? null;
                $add(page_url('hobbies', $row['slug']), 'monthly', '0.5', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
            }
            foreach (db()->query('SELECT slug, updated_at, published_at, created_at FROM posts WHERE is_published = 1 ORDER BY COALESCE(published_at, created_at) DESC, id DESC')->fetchAll() as $row) {
                $mod = $row['updated_at'] ?? $row['published_at'] ?? $row['created_at'] ?? null;
                $add(page_url('blog', $row['slug']), 'monthly', '0.7', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
            }
        } catch (\Throwable) {
            // empty sections ok
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $item) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
            if (!empty($item['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($item['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . htmlspecialchars($item['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</changefreq>\n";
            $xml .= '    <priority>' . htmlspecialchars($item['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        $response = View::html($xml, 200, 'application/xml; charset=UTF-8');
        return $response->withHeader('X-Robots-Tag', 'noindex');
    }
}
