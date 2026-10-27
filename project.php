<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    exit('Проект не найден');
}

$stmt = db()->prepare(
    'SELECT * FROM projects WHERE slug = ? AND is_published = 1 LIMIT 1'
);
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Проект не найден</h1><p><a href="' . e(base_url('projects.php')) . '">К списку проектов</a></p></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$pageTitle = $project['title'];

$shots = db()->prepare(
    'SELECT * FROM project_screenshots WHERE project_id = ? ORDER BY sort_order ASC, id ASC'
);
$shots->execute([(int) $project['id']]);
$screenshots = $shots->fetchAll();

require __DIR__ . '/templates/header.php';
?>

<article>
  <section class="page-hero">
    <div class="container narrow">
      <a class="text-link reveal" href="<?= e(base_url('projects.php')) ?>">← Все проекты</a>
      <h1 class="reveal" style="--delay:.05s"><?= e($project['title']) ?></h1>
      <p class="lead reveal" style="--delay:.1s"><?= e($project['short_description']) ?></p>
      <div class="meta-row reveal" style="--delay:.15s">
        <?php if ($project['year']): ?><span><?= e($project['year']) ?></span><?php endif; ?>
        <?php if ($project['role']): ?><span><?= e($project['role']) ?></span><?php endif; ?>
        <?php if ($project['stack']): ?><span><?= e($project['stack']) ?></span><?php endif; ?>
      </div>
      <?php if ($project['demo_url']): ?>
        <p class="reveal" style="--delay:.2s"><a class="btn btn-primary" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener">Открыть проект</a></p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($project['cover_image']): ?>
    <section class="bleed-media reveal">
      <img src="<?= e(upload_url($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>">
    </section>
  <?php endif; ?>

  <section class="section">
    <div class="container narrow prose reveal">
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
                <img src="<?= e(upload_url($shot['image_path'])) ?>" alt="<?= e($shot['caption'] ?: $project['title']) ?>" loading="lazy">
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
