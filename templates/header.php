<?php

declare(strict_types=1);

$fullName = setting('full_name', 'Килин Вячеслав Александрович');
$title = setting('title', 'Fullstack Web Разработчик');
$metaDescription = setting('meta_description', 'Портфолио разработчика PHP и Bitrix Framework');
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . $fullName : $fullName . ' · ' . $title;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($metaDescription) ?>">
  <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
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
</head>
<body>
  <div class="noise" aria-hidden="true"></div>
  <header class="site-header" data-header>
    <div class="container header-inner">
      <a class="brand" href="<?= e(base_url()) ?>">
        <span class="brand-mark">KV</span>
        <span class="brand-text">
          <strong><?= e(explode(' ', $fullName)[0] . ' ' . (explode(' ', $fullName)[1] ?? '')) ?></strong>
          <small><?= e($title) ?></small>
        </span>
      </a>
      <?php
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $isHome = preg_match('#/(index\.php)?$#', $uriPath) === 1
            && !str_contains($uriPath, 'projects')
            && !str_contains($uriPath, 'integrations')
            && !str_contains($uriPath, 'certificates')
            && !str_contains($uriPath, 'hobb')
            && !str_contains($uriPath, 'admin');
      ?>
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
          <a href="<?= e(base_url()) ?>" class="<?= $isHome ? 'is-active' : '' ?>">Главная</a>
          <a href="<?= e(base_url('projects.php')) ?>" class="<?= is_active_nav('projects') ? 'is-active' : '' ?>">Проекты</a>
          <a href="<?= e(base_url('integrations.php')) ?>" class="<?= is_active_nav('integrations') ? 'is-active' : '' ?>">Интеграции</a>
          <a href="<?= e(base_url('hobbies.php')) ?>" class="<?= is_active_nav('hobb') ? 'is-active' : '' ?>">Хобби</a>
          <a href="<?= e(base_url('certificates.php')) ?>" class="<?= is_active_nav('certificates') ? 'is-active' : '' ?>">Сертификаты</a>
          <a class="nav-cta" href="#contact">Связаться</a>
        </nav>
      </div>
    </div>
  </header>
  <main>
