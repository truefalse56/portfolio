<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Интеграции';

$integrations = db()->query(
    'SELECT id, title, slug, category, short_description, cover_image
     FROM integrations WHERE is_published = 1
     ORDER BY sort_order ASC, id DESC'
)->fetchAll();

require __DIR__ . '/templates/header.php';
?>

<section class="page-hero">
  <div class="container">
    <p class="eyebrow reveal">Битрикс24 CRM</p>
    <h1 class="reveal" style="--delay:.06s">Интеграции и кастомизация</h1>
    <p class="lead reveal" style="--delay:.12s">События CRM, табы в карточках, валидация полей, публичная часть и примеры кода.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="integration-grid integration-grid--wide">
      <?php foreach ($integrations as $i => $item): ?>
        <a class="integration-item reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s" href="<?= e(base_url('integration.php?slug=' . urlencode($item['slug']))) ?>">
          <?php if ($item['cover_image']): ?>
            <div class="integration-cover">
              <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="" loading="lazy">
            </div>
          <?php endif; ?>
          <span class="chip"><?= e($item['category']) ?></span>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['short_description']) ?></p>
          <span class="text-link">Смотреть решение</span>
        </a>
      <?php endforeach; ?>
      <?php if (!$integrations): ?>
        <p class="empty-note">Интеграции ещё не добавлены.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
