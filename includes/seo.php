<?php

declare(strict_types=1);

/**
 * Origin сайта (схема + хост).
 */
function site_origin(): string
{
    static $origin = null;
    if ($origin !== null) {
        return $origin;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443'
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

    return $origin = $scheme . '://' . $host;
}

/**
 * Абсолютный URL. Принимает путь через base_url() или относительный сегмент.
 */
function absolute_url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    if ($path === '' || $path === '/') {
        $path = base_url();
    } elseif (!str_starts_with($path, '/')) {
        $path = base_url($path);
    }

    return rtrim(site_origin(), '/') . $path;
}

/**
 * Публичные ЧПУ-маршруты.
 */
function page_url(string $page, ?string $slug = null): string
{
    $slug = $slug !== null ? rawurlencode($slug) : null;

    return match ($page) {
        'home', '' => base_url(),
        'projects' => $slug ? base_url('projects/' . $slug) : base_url('projects'),
        'integrations' => $slug ? base_url('integrations/' . $slug) : base_url('integrations'),
        'hobbies' => $slug ? base_url('hobbies/' . $slug) : base_url('hobbies'),
        'certificates' => base_url('certificates'),
        'sitemap' => base_url('sitemap.xml'),
        default => base_url(ltrim($page, '/')),
    };
}

/**
 * Нормализация старых путей (*.php) к ЧПУ — для CTA из настроек.
 */
function public_path(string $path): string
{
    $path = trim($path);
    if ($path === '' || str_starts_with($path, '#') || preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $path = ltrim($path, '/');
    $map = [
        'index.php' => '',
        'projects.php' => 'projects',
        'integrations.php' => 'integrations',
        'hobbies.php' => 'hobbies',
        'certificates.php' => 'certificates',
    ];

    if (isset($map[$path])) {
        return page_url($map[$path] === '' ? 'home' : $map[$path]);
    }

    if (preg_match('#^project\.php\?slug=([^&]+)$#', $path, $m)) {
        return page_url('projects', urldecode($m[1]));
    }
    if (preg_match('#^integration\.php\?slug=([^&]+)$#', $path, $m)) {
        return page_url('integrations', urldecode($m[1]));
    }
    if (preg_match('#^hobby\.php\?slug=([^&]+)$#', $path, $m)) {
        return page_url('hobbies', urldecode($m[1]));
    }

    return base_url($path);
}

/**
 * JSON-LD Person для владельца портфолио.
 */
function schema_person(): array
{
    $fullName = setting('full_name', 'Килин Вячеслав Александрович') ?? '';
    $jobTitle = setting('title', 'Fullstack Web Разработчик') ?? '';
    $email = setting('email', '') ?? '';
    $phone = setting('phone', '') ?? '';
    $telegram = setting('telegram', '') ?? '';
    $github = setting('github', '') ?? '';
    $location = setting('location', '') ?? '';
    $about = setting('about', '') ?? '';

    $sameAs = array_values(array_filter([
        $github !== '' ? $github : null,
        $telegram !== ''
            ? (str_starts_with($telegram, 'http') ? $telegram : 'https://t.me/' . ltrim($telegram, '@'))
            : null,
    ]));

    $person = [
        '@type' => 'Person',
        '@id' => absolute_url() . '#person',
        'name' => $fullName,
        'jobTitle' => $jobTitle,
        'url' => absolute_url(),
        'description' => excerpt($about !== '' ? $about : ($jobTitle . '. PHP, Bitrix Framework, Битрикс24 CRM.'), 300),
    ];

    if ($email !== '') {
        $person['email'] = $email;
    }
    if ($phone !== '') {
        $person['telephone'] = preg_replace('/\s+/', '', $phone) ?: $phone;
    }
    if ($location !== '') {
        $person['address'] = [
            '@type' => 'PostalAddress',
            'addressLocality' => $location,
            'addressCountry' => 'RU',
        ];
    }
    if ($sameAs !== []) {
        $person['sameAs'] = $sameAs;
    }
    $person['knowsAbout'] = [
        'PHP',
        'Bitrix Framework',
        'Битрикс24 CRM',
        'Веб-разработка',
        'Интеграции CRM',
    ];

    return $person;
}

/**
 * Базовый граф WebSite + Person.
 */
function schema_website(): array
{
    $fullName = setting('full_name', 'Килин Вячеслав Александрович') ?? '';
    $description = setting('meta_description', 'Портфолио разработчика PHP и Bitrix Framework') ?? '';

    return [
        '@type' => 'WebSite',
        '@id' => absolute_url() . '#website',
        'name' => $fullName . ' — портфолио',
        'url' => absolute_url(),
        'description' => $description,
        'inLanguage' => 'ru-RU',
        'publisher' => ['@id' => absolute_url() . '#person'],
    ];
}

/**
 * BreadcrumbList schema из массива [['name'=>..., 'url'=>...], ...].
 */
function schema_breadcrumbs(array $crumbs): ?array
{
    if ($crumbs === []) {
        return null;
    }

    $items = [];
    foreach ($crumbs as $i => $crumb) {
        $entry = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $crumb['name'],
        ];
        if (!empty($crumb['url'])) {
            $entry['item'] = absolute_url($crumb['url']);
        }
        $items[] = $entry;
    }

    return [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

/**
 * Вывод JSON-LD.
 *
 * @param list<array<string, mixed>> $graphs
 */
function render_json_ld(array $graphs): string
{
    $graphs = array_values(array_filter($graphs));
    if ($graphs === []) {
        return '';
    }

    $payload = [
        '@context' => 'https://schema.org',
        '@graph' => $graphs,
    ];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    if ($json === false) {
        return '';
    }

    return '<script type="application/ld+json">' . $json . '</script>' . "\n";
}

/**
 * HTML хлебных крошек.
 *
 * @param list<array{name:string,url?:string}> $crumbs
 */
function render_breadcrumbs(array $crumbs): string
{
    if (count($crumbs) < 2) {
        return '';
    }

    $html = '<nav aria-label="Хлебные крошки"><ol class="breadcrumb">';
    $last = count($crumbs) - 1;
    foreach ($crumbs as $i => $crumb) {
        $name = e($crumb['name']);
        if ($i === $last || empty($crumb['url'])) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . $name . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . e($crumb['url']) . '">' . $name . '</a></li>';
        }
    }
    $html .= '</ol></nav>';

    return $html;
}
