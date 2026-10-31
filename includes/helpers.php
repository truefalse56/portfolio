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
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir = str_replace('\\', '/', dirname($script));

        // Front controller: /index.php → корень; /subdir/index.php → /subdir
        if (str_ends_with($script, '/index.php') || $script === '/index.php' || $script === 'index.php') {
            $dir = str_replace('\\', '/', dirname($script));
        }

        $base = rtrim($dir, '/');
        if ($base === '' || $base === '.') {
            $base = '';
        }
    }

    $path = ltrim($path, '/');
    return $path === '' ? ($base === '' ? '/' : $base . '/') : $base . '/' . $path;
}

function admin_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return $path === '' ? base_url('admin') : base_url('admin/' . $path);
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
    $path = rtrim($path, '/') ?: '/';

    if ($needle === '/' || $needle === 'home') {
        return $path === '/' || str_ends_with($path, '/index.php');
    }

    $aliases = [
        'projects' => ['/projects', 'projects.php', 'project.php'],
        'integrations' => ['/integrations', 'integrations.php', 'integration.php'],
        'hobbies' => ['/hobbies', 'hobbies.php', 'hobby.php', 'hobb'],
        'blog' => ['/blog'],
        'certificates' => ['/certificates', 'certificates.php'],
    ];

    $needles = $aliases[$needle] ?? [$needle];
    foreach ($needles as $n) {
        if (str_contains($path, $n)) {
            return true;
        }
    }

    return false;
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

function format_date_ru(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return '';
    }
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
    ];

    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function post_date(array $post): string
{
    $raw = $post['published_at'] ?? $post['created_at'] ?? null;

    return format_date_ru(is_string($raw) ? $raw : null);
}

function post_date_iso(array $post): string
{
    $raw = $post['published_at'] ?? $post['created_at'] ?? null;
    if (!is_string($raw) || $raw === '') {
        return '';
    }
    $ts = strtotime($raw);

    return $ts ? date('Y-m-d', $ts) : '';
}

/**
 * Краткое описание из первого абзаца, без заголовков, списков и кода.
 */
function article_excerpt(string $text, int $length = 240): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $withoutCode = preg_replace('/```[a-zA-Z0-9_+#-]*[ \t]*\n.*?```/s', "\n\n", $text) ?? $text;
    $blocks = preg_split('/\n{2,}/', trim($withoutCode)) ?: [];

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '' || preg_match('/^#{1,3}[ \t]/', $block) === 1) {
            continue;
        }
        if (preg_match('/^(?:[ \t]*([-*]|\d+\.)[ \t]+.*\n?)+$/', $block) === 1) {
            continue;
        }

        return excerpt(article_plain_text($block), $length);
    }

    return excerpt(article_plain_text($text), $length);
}

/**
 * Текст статьи без разметки — для краткого описания и meta.
 */
function article_plain_text(string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/```[a-zA-Z0-9_+#-]*[ \t]*\n.*?```/s', ' ', $text) ?? $text;
    $text = preg_replace('/^#{1,3}[ \t]+/m', '', $text) ?? $text;
    $text = preg_replace('/^[ \t]*([-*]|\d+\.)[ \t]+/m', '', $text) ?? $text;
    $text = str_replace(['**', '`'], '', $text);
    $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text) ?? $text;
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
}

/**
 * @return array{0: string, 1: string} [класс Prism, подпись]
 */
function code_language(string $raw): array
{
    $key = strtolower(trim($raw));
    $map = [
        '' => ['none', 'Text'],
        'text' => ['none', 'Text'],
        'txt' => ['none', 'Text'],
        'plaintext' => ['none', 'Text'],
        'none' => ['none', 'Text'],
        'php' => ['php', 'PHP'],
        'js' => ['javascript', 'JavaScript'],
        'javascript' => ['javascript', 'JavaScript'],
        'ts' => ['typescript', 'TypeScript'],
        'typescript' => ['typescript', 'TypeScript'],
        'html' => ['markup', 'HTML'],
        'xml' => ['markup', 'XML'],
        'svg' => ['markup', 'SVG'],
        'markup' => ['markup', 'HTML'],
        'css' => ['css', 'CSS'],
        'scss' => ['scss', 'SCSS'],
        'sql' => ['sql', 'SQL'],
        'mysql' => ['sql', 'SQL'],
        'bash' => ['bash', 'Bash'],
        'sh' => ['bash', 'Bash'],
        'shell' => ['bash', 'Bash'],
        'json' => ['json', 'JSON'],
        'python' => ['python', 'Python'],
        'py' => ['python', 'Python'],
        'yaml' => ['yaml', 'YAML'],
        'yml' => ['yaml', 'YAML'],
        'go' => ['go', 'Go'],
        'java' => ['java', 'Java'],
        'csharp' => ['csharp', 'C#'],
        'cs' => ['csharp', 'C#'],
        'cpp' => ['cpp', 'C++'],
        'c' => ['c', 'C'],
        'ruby' => ['ruby', 'Ruby'],
        'rb' => ['ruby', 'Ruby'],
        'twig' => ['twig', 'Twig'],
        'graphql' => ['graphql', 'GraphQL'],
        'ini' => ['ini', 'INI'],
    ];

    if (isset($map[$key])) {
        return $map[$key];
    }

    if (preg_match('/^[a-z][a-z0-9_+-]{0,20}$/', $key) === 1) {
        return [$key, strtoupper($key)];
    }

    return ['none', 'Text'];
}

/**
 * Статья: абзацы, заголовки, списки и блоки кода ```язык.
 */
function render_article(string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $parts = preg_split(
        '/```([a-zA-Z0-9_+#-]*)[ \t]*\n(.*?)```/s',
        $text,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );
    if ($parts === false) {
        return '';
    }

    $html = '';
    $count = count($parts);
    for ($i = 0; $i < $count; $i++) {
        if ($i % 3 === 0) {
            $chunk = trim($parts[$i]);
            if ($chunk === '') {
                continue;
            }
            $blocks = preg_split('/\n{2,}/', $chunk) ?: [];
            foreach ($blocks as $block) {
                $block = trim($block);
                if ($block !== '') {
                    $html .= render_text_block($block);
                }
            }
            continue;
        }

        [$prism, $label] = code_language($parts[$i]);
        $code = rtrim((string) ($parts[$i + 1] ?? ''), "\n");
        $html .= code_block_html($prism, $label, $code);
        $i++;
    }

    return $html;
}

function render_text_block(string $chunk): string
{
    $lines = explode("\n", $chunk);
    $html = '';
    $i = 0;
    $count = count($lines);

    while ($i < $count) {
        $line = $lines[$i];

        if (preg_match('/^(#{1,3})[ \t]+(.+)$/', $line, $m) === 1) {
            $tag = match (strlen($m[1])) {
                1 => 'h2',
                2 => 'h3',
                default => 'h4',
            };
            $html .= '<' . $tag . '>' . format_inline(e(trim($m[2]))) . '</' . $tag . '>';
            $i++;
            continue;
        }

        if (preg_match('/^[ \t]*[-*][ \t]+/', $line) === 1) {
            $html .= '<ul>';
            while ($i < $count && preg_match('/^[ \t]*[-*][ \t]+(.+)$/', $lines[$i], $m) === 1) {
                $html .= '<li>' . format_inline(e($m[1])) . '</li>';
                $i++;
            }
            $html .= '</ul>';
            continue;
        }

        if (preg_match('/^[ \t]*\d+\.[ \t]+/', $line) === 1) {
            $html .= '<ol>';
            while ($i < $count && preg_match('/^[ \t]*\d+\.[ \t]+(.+)$/', $lines[$i], $m) === 1) {
                $html .= '<li>' . format_inline(e($m[1])) . '</li>';
                $i++;
            }
            $html .= '</ol>';
            continue;
        }

        $para = [];
        while (
            $i < $count
            && preg_match('/^(#{1,3})[ \t]+/', $lines[$i]) !== 1
            && preg_match('/^[ \t]*([-*]|\d+\.)[ \t]+/', $lines[$i]) !== 1
        ) {
            $para[] = $lines[$i];
            $i++;
        }
        $paragraph = trim(implode("\n", $para));
        if ($paragraph !== '') {
            $html .= '<p>' . nl2br(format_inline(e($paragraph))) . '</p>';
        }
    }

    return $html;
}

function format_inline(string $escaped): string
{
    $parts = preg_split('/`([^`\n]+)`/', $escaped, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return $escaped;
    }

    $html = '';
    foreach ($parts as $i => $part) {
        if ($i % 2 === 1) {
            $html .= '<code>' . $part . '</code>';
            continue;
        }
        $part = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $part) ?? $part;
        $part = preg_replace_callback(
            '/\[([^\]\n]+)\]\((https?:\/\/[^\s)]+)\)/',
            static fn (array $m): string => '<a href="' . $m[2] . '" target="_blank" rel="noopener noreferrer">' . $m[1] . '</a>',
            $part
        ) ?? $part;
        $html .= $part;
    }

    return $html;
}

function code_block_html(string $language, string $label, string $code): string
{
    return '<div class="code-block">'
        . '<div class="code-toolbar"><span>' . e($label) . '</span>'
        . '<button type="button" class="copy-btn" data-copy>Копировать</button></div>'
        . '<pre><code class="language-' . e($language) . '">' . e($code) . '</code></pre>'
        . '</div>';
}
