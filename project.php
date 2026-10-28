<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    $metaDescription = 'Запрошенный проект не найден.';
    $robots = 'noindex, follow';
    $canonicalPath = page_url('projects');
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Проект не найден</h1><p><a href="' . e(page_url('projects')) . '">К списку проектов</a></p></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$stmt = db()->prepare(
    'SELECT * FROM projects WHERE slug = ? AND is_published = 1 LIMIT 1'
);
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    $metaDescription = 'Запрошенный проект не найден.';
    $robots = 'noindex, follow';
    $canonicalPath = page_url('projects');
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Проект не найден</h1><p><a href="' . e(page_url('projects')) . '">К списку проектов</a></p></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$pageTitle = $project['title'];
$metaDescription = $project['short_description']
    ?: excerpt((string) $project['description'], 160);
$canonicalPath = page_url('projects', $project['slug']);
$ogType = 'article';
$ogImage = $project['cover_image'] ? upload_url($project['cover_image']) : '';
$breadcrumbs = [
    ['name' => 'Главная', 'url' => page_url('home')],
    ['name' => 'Проекты', 'url' => page_url('projects')],
    ['name' => $project['title']],
];

$creativeWork = [
    '@type' => 'CreativeWork',
    '@id' => absolute_url($canonicalPath) . '#work',
    'name' => $project['title'],
    'headline' => $project['title'],
    'description' => $metaDescription,
    'url' => absolute_url($canonicalPath),
    'inLanguage' => 'ru-RU',
    'author' => ['@id' => absolute_url() . '#person'],
    'creator' => ['@id' => absolute_url() . '#person'],
    'isPartOf' => ['@id' => absolute_url() . '#website'],
];
if ($ogImage) {
    $creativeWork['image'] = absolute_url($ogImage);
}
if (!empty($project['year'])) {
    $creativeWork['dateCreated'] = (string) $project['year'];
}
if (!empty($project['stack'])) {
    $creativeWork['keywords'] = $project['stack'];
}
if (!empty($project['demo_url'])) {
    $creativeWork['url'] = absolute_url($canonicalPath);
    $creativeWork['sameAs'] = [$project['demo_url']];
}

$schemaGraphs = [
    schema_person(),
    schema_website(),
    schema_breadcrumbs($breadcrumbs),
    $creativeWork,
];

$shots = db()->prepare(
    'SELECT * FROM project_screenshots WHERE project_id = ? ORDER BY sort_order ASC, id ASC'
);
$shots->execute([(int) $project['id']]);
$screenshots = $shots->fetchAll();

require __DIR__ . '/templates/header.php';
?>

<article itemscope itemtype="https://schema.org/CreativeWork">
  <section class="page-hero">
    <div class="container narrow">
      <?= render_breadcrumbs($breadcrumbs) ?>
      <a class="text-link reveal" href="<?= e(page_url('projects')) ?>">← Все проекты</a>
      <h1 class="reveal" style="--delay:.05s" itemprop="name"><?= e($project['title']) ?></h1>
      <p class="lead reveal" style="--delay:.1s" itemprop="description"><?= e($project['short_description']) ?></p>
      <div class="meta-row reveal" style="--delay:.15s">
        <?php if ($project['year']): ?><span><?= e($project['year']) ?></span><?php endif; ?>
        <?php if ($project['role']): ?><span><?= e($project['role']) ?></span><?php endif; ?>
        <?php if ($project['stack']): ?><span itemprop="keywords"><?= e($project['stack']) ?></span><?php endif; ?>
      </div>
      <?php if ($project['demo_url']): ?>
        <p class="reveal" style="--delay:.2s"><a class="btn btn-primary" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener">Открыть проект</a></p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($project['cover_image']): ?>
    <section class="bleed-media reveal">
      <img src="<?= e(upload_url($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>" itemprop="image" width="1200" height="514">
    </section>
  <?php endif; ?>

  <section class="section">
    <div class="container narrow prose reveal" itemprop="text">
      <?= markdown_lite($project['description']) ?>
    </div>
  </section>

  <?php if ($screenshots): ?>
    <section class="section section-tint">
      <div class="container">
        <h2 class="reveal">Скриншоты</h2>
        <div class="shot-grid">
          <?php foreach ($screenshots as $i => $shot): ?>
            <figure class="shot reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s">
              <a href="<?= e(upload_url($shot['image_path'])) ?>" target="_blank" rel="noopener">
                <img src="<?= e(upload_url($shot['image_path'])) ?>" alt="<?= e($shot['caption'] ?: $project['title']) ?>" loading="lazy" width="800" height="500">
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
