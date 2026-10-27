<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Сертификаты';

$certificates = db()->query(
    'SELECT * FROM certificates WHERE is_published = 1
     ORDER BY sort_order ASC, issued_at DESC, id DESC'
)->fetchAll();

require __DIR__ . '/templates/header.php';
?>

<section class="page-hero">
  <div class="container">
    <p class="eyebrow reveal">Квалификация</p>
    <h1 class="reveal" style="--delay:.06s">Сертификаты</h1>
    <p class="lead reveal" style="--delay:.12s">Документы о сдаче экзаменов и подтверждённых компетенциях.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cert-grid">
      <?php foreach ($certificates as $i => $cert): ?>
        <article class="cert-item reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s">
          <div class="cert-preview">
            <?php if ($cert['image_path']): ?>
              <a href="<?= e(upload_url($cert['image_path'])) ?>" target="_blank" rel="noopener">
                <img src="<?= e(upload_url($cert['image_path'])) ?>" alt="<?= e($cert['title']) ?>" loading="lazy">
              </a>
            <?php else: ?>
              <div class="media-placeholder media-placeholder--cert" data-label="★"></div>
            <?php endif; ?>
          </div>
          <div class="cert-body">
            <div class="work-meta">
              <?php if ($cert['issuer']): ?><span><?= e($cert['issuer']) ?></span><?php endif; ?>
              <?php if ($cert['issued_at']): ?><span><?= e(format_date($cert['issued_at'])) ?></span><?php endif; ?>
            </div>
            <h3><?= e($cert['title']) ?></h3>
            <?php if ($cert['description']): ?>
              <p><?= e($cert['description']) ?></p>
            <?php endif; ?>
            <?php if ($cert['file_path']): ?>
              <a class="text-link" href="<?= e(upload_url($cert['file_path'])) ?>" target="_blank" rel="noopener">Скачать файл</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if (!$certificates): ?>
        <p class="empty-note">Добавьте грамоты через административное меню.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
