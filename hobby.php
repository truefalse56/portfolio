<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(404);
    exit('Запись не найдена');
}

$stmt = db()->prepare(
    'SELECT * FROM hobbies WHERE slug = ? AND is_published = 1 LIMIT 1'
);
$stmt->execute([$slug]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    $pageTitle = 'Не найдено';
    require __DIR__ . '/templates/header.php';
    echo '<section class="section"><div class="container"><h1>Запись не найдена</h1><p><a href="' . e(base_url('hobbies.php')) . '">К разделу «Хобби»</a></p></div></section>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$pageTitle = $item['title'];

$imagesStmt = db()->prepare(
    'SELECT * FROM hobby_images WHERE hobby_id = ? ORDER BY sort_order ASC, id ASC'
);
$imagesStmt->execute([(int) $item['id']]);
$images = $imagesStmt->fetchAll();

// Обложка в слайдере, если отдельных фото ещё нет
$slides = $images;
if (!$slides && $item['cover_image']) {
    $slides = [[
        'image_path' => $item['cover_image'],
        'caption' => $item['title'],
    ]];
}

require __DIR__ . '/templates/header.php';
?>

<article>
  <section class="page-hero">
    <div class="container narrow">
      <a class="text-link reveal" href="<?= e(base_url('hobbies.php')) ?>">← Все хобби</a>
      <h1 class="reveal" style="--delay:.05s"><?= e($item['title']) ?></h1>
      <?php if ($item['short_description']): ?>
        <p class="lead reveal" style="--delay:.1s"><?= e($item['short_description']) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($slides): ?>
    <section class="section" style="padding-top:0">
      <div class="container">
        <div class="slider reveal" data-slider aria-roledescription="carousel" aria-label="<?= e($item['title']) ?>">
          <div class="slider-viewport">
            <div class="slider-track" data-slider-track>
              <?php foreach ($slides as $i => $slide): ?>
                <figure class="slider-slide" data-slider-slide <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
                  <img src="<?= e(upload_url($slide['image_path'])) ?>" alt="<?= e($slide['caption'] ?: $item['title']) ?>" <?= $i === 0 ? '' : 'loading="lazy"' ?>>
                  <?php if (!empty($slide['caption'])): ?>
                    <figcaption><?= e($slide['caption']) ?></figcaption>
                  <?php endif; ?>
                </figure>
              <?php endforeach; ?>
            </div>
          </div>
          <?php if (count($slides) > 1): ?>
            <button type="button" class="slider-btn slider-btn--prev" data-slider-prev aria-label="Предыдущее фото">‹</button>
            <button type="button" class="slider-btn slider-btn--next" data-slider-next aria-label="Следующее фото">›</button>
            <div class="slider-dots" data-slider-dots>
              <?php foreach ($slides as $i => $_): ?>
                <button type="button" class="slider-dot <?= $i === 0 ? 'is-active' : '' ?>" data-slider-dot="<?= $i ?>" aria-label="Фото <?= $i + 1 ?>"></button>
              <?php endforeach; ?>
            </div>
            <div class="slider-counter"><span data-slider-current>1</span> / <?= count($slides) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="section" style="padding-top:<?= $slides ? '1rem' : '3rem' ?>">
    <div class="container narrow prose reveal">
      <?= markdown_lite($item['description']) ?>
    </div>
  </section>
</article>

<?php require __DIR__ . '/templates/footer.php'; ?>
