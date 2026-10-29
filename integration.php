<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    $metaDescription = 'Запрошенная интеграция не найдена.';
    $robots = 'noindex, follow';
    $canonicalPath = page_url('integrations');
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Интеграция не найдена</h1><p><a href="' . e(page_url('integrations')) . '">К списку интеграций</a></p></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$stmt = db()->prepare(
    'SELECT * FROM integrations WHERE slug = ? AND is_published = 1 LIMIT 1'
);
$stmt->execute([$slug]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    $metaDescription = 'Запрошенная интеграция не найдена.';
    $robots = 'noindex, follow';
    $canonicalPath = page_url('integrations');
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Интеграция не найдена</h1><p><a href="' . e(page_url('integrations')) . '">К списку интеграций</a></p></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$pageTitle = $item['title'];
$metaDescription = $item['short_description']
    ?: excerpt((string) $item['description'], 160);
$canonicalPath = page_url('integrations', $item['slug']);
$ogType = 'article';
$ogImage = $item['cover_image'] ? upload_url($item['cover_image']) : '';
$breadcrumbs = [
    ['name' => 'Главная', 'url' => page_url('home')],
    ['name' => 'Интеграции', 'url' => page_url('integrations')],
    ['name' => $item['title']],
];

$techArticle = [
    '@type' => 'TechArticle',
    '@id' => absolute_url($canonicalPath) . '#article',
    'headline' => $item['title'],
    'name' => $item['title'],
    'description' => $metaDescription,
    'url' => absolute_url($canonicalPath),
    'inLanguage' => 'ru-RU',
    'author' => ['@id' => absolute_url() . '#person'],
    'creator' => ['@id' => absolute_url() . '#person'],
    'isPartOf' => ['@id' => absolute_url() . '#website'],
    'about' => $item['category'] ?: 'Битрикс24 CRM',
];
if ($ogImage) {
    $techArticle['image'] = absolute_url($ogImage);
}

$schemaGraphs = [
    schema_person(),
    schema_website(),
    schema_breadcrumbs($breadcrumbs),
    $techArticle,
];

$features = [];
if (!empty($item['features'])) {
    $decoded = json_decode($item['features'], true);
    if (is_array($decoded)) {
        $features = $decoded;
    }
}

$shots = db()->prepare(
    'SELECT * FROM integration_screenshots WHERE integration_id = ? ORDER BY sort_order ASC, id ASC'
);
$shots->execute([(int) $item['id']]);
$screenshots = $shots->fetchAll();

$lang = preg_replace('/[^a-z0-9+-]/i', '', $item['code_language'] ?: 'php') ?: 'php';

require __DIR__ . '/templates/header.php';
?>

<article>
  <section class="page-hero">
    <div class="container narrow">
      <?= render_breadcrumbs($breadcrumbs) ?>
      <a class="text-link reveal" href="<?= e(page_url('integrations')) ?>">← Все интеграции</a>
      <span class="chip reveal" style="--delay:.04s"><?= e($item['category']) ?></span>
      <h1 class="reveal" style="--delay:.08s"><?= e($item['title']) ?></h1>
      <p class="lead reveal" style="--delay:.12s"><?= e($item['short_description']) ?></p>
    </div>
  </section>

  <?php if ($item['cover_image']): ?>
    <section class="bleed-media reveal">
      <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" width="1200" height="514">
    </section>
  <?php endif; ?>

  <section class="section">
    <div class="container">
      <div class="row g-5 align-items-start">
        <div class="col-lg-7 prose reveal">
          <?= markdown_lite($item['description']) ?>
        </div>
        <?php if ($features): ?>
          <div class="col-lg-5">
            <aside class="feature-panel reveal" style="--delay:.1s">
              <h2>Что сделано</h2>
              <ul>
                <?php foreach ($features as $feature): ?>
                  <li><?= e((string) $feature) ?></li>
                <?php endforeach; ?>
              </ul>
            </aside>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($item['code_example'])): ?>
    <section class="section section-tint">
      <div class="container narrow">
        <div class="section-head reveal">
          <div>
            <p class="eyebrow">Пример кода</p>
            <h2>Реализация</h2>
          </div>
        </div>
        <div class="code-block reveal" style="--delay:.08s">
          <div class="code-toolbar">
            <span><?= e(strtoupper($lang)) ?></span>
            <button type="button" class="copy-btn" data-copy>Копировать</button>
          </div>
          <pre><code class="language-<?= e($lang) ?>"><?= e($item['code_example']) ?></code></pre>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($screenshots): ?>
    <section class="section">
      <div class="container">
        <h2 class="reveal">Скриншоты</h2>
        <div class="row g-4 mt-1">
          <?php foreach ($screenshots as $i => $shot): ?>
            <div class="col-md-6">
              <figure class="shot reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s">
                <a href="<?= e(upload_url($shot['image_path'])) ?>" target="_blank" rel="noopener">
                  <img src="<?= e(upload_url($shot['image_path'])) ?>" alt="<?= e($shot['caption'] ?: $item['title']) ?>" loading="lazy" width="800" height="500">
                </a>
                <?php if ($shot['caption']): ?>
                  <figcaption><?= e($shot['caption']) ?></figcaption>
                <?php endif; ?>
              </figure>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</article>

<?php require __DIR__ . '/templates/footer.php'; ?>
