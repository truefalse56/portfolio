<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    exit('Интеграция не найдена');
}

$stmt = db()->prepare(
    'SELECT * FROM integrations WHERE slug = ? AND is_published = 1 LIMIT 1'
);
$stmt->execute([$slug]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Интеграция не найдена</h1></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$pageTitle = $item['title'];

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
      <a class="text-link reveal" href="<?= e(base_url('integrations.php')) ?>">← Все интеграции</a>
      <span class="chip reveal" style="--delay:.04s"><?= e($item['category']) ?></span>
      <h1 class="reveal" style="--delay:.08s"><?= e($item['title']) ?></h1>
      <p class="lead reveal" style="--delay:.12s"><?= e($item['short_description']) ?></p>
    </div>
  </section>

  <?php if ($item['cover_image']): ?>
    <section class="bleed-media reveal">
      <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>">
    </section>
  <?php endif; ?>

  <section class="section">
    <div class="container detail-grid">
      <div class="prose reveal">
        <?= markdown_lite($item['description']) ?>
      </div>
      <?php if ($features): ?>
        <aside class="feature-panel reveal" style="--delay:.1s">
          <h2>Что сделано</h2>
          <ul>
            <?php foreach ($features as $feature): ?>
              <li><?= e((string) $feature) ?></li>
            <?php endforeach; ?>
          </ul>
        </aside>
      <?php endif; ?>
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
        <div class="shot-grid">
          <?php foreach ($screenshots as $i => $shot): ?>
            <figure class="shot reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s">
              <a href="<?= e(upload_url($shot['image_path'])) ?>" target="_blank" rel="noopener">
                <img src="<?= e(upload_url($shot['image_path'])) ?>" alt="<?= e($shot['caption'] ?: $item['title']) ?>" loading="lazy">
              </a>
              <?php if ($shot['caption']): ?>
                <figcaption><?= e($shot['caption']) ?></figcaption>
              <?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</article>

<?php require __DIR__ . '/templates/footer.php'; ?>
