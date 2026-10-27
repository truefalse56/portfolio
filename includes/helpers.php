<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function base_url(string $path = ''): string
{
    static $base = null;

    if ($base === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($script));

        // /admin/... → корень сайта
        if (str_contains($dir, '/admin')) {
            $dir = preg_replace('#/admin(?:/.*)?$#', '', $dir) ?: '';
        }

        $base = rtrim($dir, '/');
        if ($base === '' || $base === '.') {
            $base = '';
        }
    }

    $path = ltrim($path, '/');
    return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function upload_url(?string $filename): string
{
    if (!$filename) {
        return '';
    }
    if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
        return $filename;
    }
    return base_url('assets/uploads/' . ltrim($filename, '/'));
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function old(string $key, mixed $default = ''): mixed
{
    $old = $_SESSION['_old'][$key] ?? $default;
    return $old;
}

function store_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function slugify(string $text): string
{
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
        'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
        'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        'А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'e','Ж'=>'zh','З'=>'z',
        'И'=>'i','Й'=>'y','К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r',
        'С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'h','Ц'=>'ts','Ч'=>'ch','Ш'=>'sh','Щ'=>'sch',
        'Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya',
    ];
    $text = strtr($text, $map);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function excerpt(string $text, int $length = 160): string
{
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $length), " \t\n\r\0\x0B.,;") . '…';
}

function format_date(?string $date, string $format = 'd.m.Y'): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '';
}

function is_active_nav(string $needle): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    if ($needle === '/') {
        return $path === '/' || str_ends_with($path, '/index.php');
    }
    return str_contains($path, $needle);
}

function setting(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            $cache = [];
            foreach ($rows as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function markdown_lite(string $text): string
{
    $text = e($text);
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text) ?? $text;
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text) ?? $text;
    $paragraphs = preg_split('/\n{2,}/', $text) ?: [];
    $html = '';
    foreach ($paragraphs as $p) {
        $html .= '<p>' . nl2br(trim($p)) . '</p>';
    }
    return $html;
}
