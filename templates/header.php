<?php

declare(strict_types=1);

$fullName = setting('full_name', 'Килин Вячеслав Александрович');
$title = setting('title', 'Fullstack Web Разработчик');
$defaultDescription = setting('meta_description', 'Портфолио разработчика PHP и Bitrix Framework') ?? 'Портфолио разработчика PHP и Bitrix Framework';

$metaDescription = isset($metaDescription) && $metaDescription !== ''
    ? excerpt((string) $metaDescription, 160)
    : excerpt($defaultDescription, 160);

$documentTitle = isset($pageTitle) && $pageTitle !== ''
    ? (string) $pageTitle . ' — ' . $fullName
    : $fullName . ' · ' . $title;

$canonicalUrl = absolute_url($canonicalPath ?? page_url('home'));
$ogType = $ogType ?? 'website';
$ogImage = isset($ogImage) && $ogImage !== '' ? absolute_url((string) $ogImage) : '';
$robots = $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$breadcrumbs = $breadcrumbs ?? [];
$schemaGraphs = $schemaGraphs ?? [];

if ($schemaGraphs === []) {
    $schemaGraphs = [schema_person(), schema_website()];
    $crumbSchema = schema_breadcrumbs($breadcrumbs);
    if ($crumbSchema !== null) {
        $schemaGraphs[] = $crumbSchema;
    }
}

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uriPath = rtrim($uriPath, '/') ?: '/';
$isHome = $uriPath === '/' || str_ends_with($uriPath, '/index.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($documentTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <meta name="robots" content="<?= e($robots) ?>">
  <meta name="author" content="<?= e($fullName) ?>">
  <meta name="theme-color" content="#0f766e" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#0f171c" media="(prefers-color-scheme: dark)">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">

  <meta property="og:locale" content="ru_RU">
  <meta property="og:type" content="<?= e($ogType) ?>">
  <meta property="og:title" content="<?= e($documentTitle) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta property="og:site_name" content="<?= e($fullName) ?>">
  <?php if ($ogImage !== ''): ?>
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:image:alt" content="<?= e(isset($pageTitle) ? (string) $pageTitle : $fullName) ?>">
  <?php endif; ?>

  <meta name="twitter:card" content="<?= $ogImage !== '' ? 'summary_large_image' : 'summary' ?>">
  <meta name="twitter:title" content="<?= e($documentTitle) ?>">
  <meta name="twitter:description" content="<?= e($metaDescription) ?>">
  <?php if ($ogImage !== ''): ?>
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
  <?php endif; ?>

  <link rel="icon" type="image/x-icon" href="<?= e(absolute_url('favicon.ico')) ?>">
  <script>
    (function () {
      try {
        if (localStorage.getItem('theme') === 'dark') {
          document.documentElement.setAttribute('data-theme', 'dark');
        }
      } catch (e) {}
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css">
  <?= render_json_ld($schemaGraphs) ?>
</head>
<body>
  <div class="noise" aria-hidden="true"></div>
  <header class="site-header" data-header>
    <div class="container header-inner">
      <a class="brand" href="<?= e(page_url('home')) ?>">
        <span class="brand-mark">KV</span>
        <span class="brand-text">
          <strong><?= e(explode(' ', $fullName)[0] . ' ' . (explode(' ', $fullName)[1] ?? '')) ?></strong>
          <small><?= e($title) ?></small>
        </span>
      </a>
      <div class="header-end">
        <button
          class="theme-toggle"
          type="button"
          aria-label="Переключить тему"
          title="Сменить тему"
          data-theme-toggle
        >
          <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 14.3A8.4 8.4 0 0 1 9.7 3 7.2 7.2 0 1 0 21 14.3z"/>
          </svg>
          <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
          </svg>
        </button>
        <button class="nav-toggle" type="button" aria-label="Меню" data-nav-toggle>
          <span></span><span></span>
        </button>
        <nav class="site-nav" data-nav>
          <a href="<?= e(page_url('home')) ?>" class="<?= $isHome ? 'is-active' : '' ?>">Главная</a>
          <a href="<?= e(page_url('projects')) ?>" class="<?= is_active_nav('projects') ? 'is-active' : '' ?>">Проекты</a>
          <a href="<?= e(page_url('integrations')) ?>" class="<?= is_active_nav('integrations') ? 'is-active' : '' ?>">Интеграции</a>
          <a href="<?= e(page_url('hobbies')) ?>" class="<?= is_active_nav('hobbies') ? 'is-active' : '' ?>">Хобби</a>
          <a href="<?= e(page_url('certificates')) ?>" class="<?= is_active_nav('certificates') ? 'is-active' : '' ?>">Сертификаты</a>
          <a class="nav-cta" href="#contact">Связаться</a>
        </nav>
      </div>
    </div>
  </header>
  <main>
