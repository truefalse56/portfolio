<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Хобби';

$hobbies = db()->query(
    'SELECT id, title, slug, short_description, cover_image,
            (SELECT COUNT(*) FROM hobby_images hi WHERE hi.hobby_id = hobbies.id) AS images_count
     FROM hobbies WHERE is_published = 1
     ORDER BY sort_order ASC, id DESC'
)->fetchAll();

require __DIR__ . '/templates/header.php';
?>

<section class="page-hero">
  <div class="container">
    <p class="eyebrow reveal">Вне кода</p>
    <h1 class="reveal" style="--delay:.06s">Хобби</h1>
    <p class="lead reveal" style="--delay:.12s">Интересы и занятия вне разработки — с фото и описанием.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="hobby-grid">
      <?php foreach ($hobbies as $i => $item): ?>
        <a class="hobby-card reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s" href="<?= e(base_url('hobby.php?slug=' . urlencode($item['slug']))) ?>">
          <div class="hobby-card-media">
            <?php if ($item['cover_image']): ?>
              <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
            <?php else: ?>
              <div class="media-placeholder" data-label="<?= e(mb_substr($item['title'], 0, 1)) ?>"></div>
            <?php endif; ?>
            <?php if ((int) $item['images_count'] > 0): ?>
              <span class="hobby-photo-badge"><?= (int) $item['images_count'] ?> фото</span>
            <?php endif; ?>
          </div>
          <div class="hobby-card-body">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['short_description']) ?></p>
            <span class="text-link">Смотреть</span>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (!$hobbies): ?>
        <p class="empty-note">Записи появятся после добавления в админ-панели.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
