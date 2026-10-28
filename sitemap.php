<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

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
$add(page_url('certificates'), 'monthly', '0.7');

try {
    $projects = db()->query(
        'SELECT slug, updated_at, created_at FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, id DESC'
    )->fetchAll();
    foreach ($projects as $row) {
        $mod = $row['updated_at'] ?? $row['created_at'] ?? null;
        $add(page_url('projects', $row['slug']), 'monthly', '0.8', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
    }

    $integrations = db()->query(
        'SELECT slug, updated_at, created_at FROM integrations WHERE is_published = 1 ORDER BY sort_order ASC, id DESC'
    )->fetchAll();
    foreach ($integrations as $row) {
        $mod = $row['updated_at'] ?? $row['created_at'] ?? null;
        $add(page_url('integrations', $row['slug']), 'monthly', '0.8', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
    }

    $hobbies = db()->query(
        'SELECT slug, updated_at, created_at FROM hobbies WHERE is_published = 1 ORDER BY sort_order ASC, id DESC'
    )->fetchAll();
    foreach ($hobbies as $row) {
        $mod = $row['updated_at'] ?? $row['created_at'] ?? null;
        $add(page_url('hobbies', $row['slug']), 'monthly', '0.5', $mod ? date('Y-m-d', strtotime((string) $mod) ?: time()) : null);
    }
} catch (Throwable) {
    // Пустой sitemap разделов допустим при ошибке БД
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $item) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if (!empty($item['lastmod'])) {
        echo '    <lastmod>' . htmlspecialchars($item['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</lastmod>\n";
    }
    echo '    <changefreq>' . htmlspecialchars($item['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</changefreq>\n";
    echo '    <priority>' . htmlspecialchars($item['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
