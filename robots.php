<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');
header('X-Robots-Tag: noindex');

$sitemap = absolute_url('sitemap.xml');

echo <<<TXT
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /config/
Disallow: /includes/
Disallow: /sql/
Disallow: /templates/
Disallow: /storage/
Disallow: /install.php
Disallow: /fix-database.php

Sitemap: {$sitemap}

TXT;
