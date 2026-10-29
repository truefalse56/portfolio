<section class="page-hero">
  <div class="container">
    <?= render_breadcrumbs($breadcrumbs) ?>
    <p class="eyebrow reveal">Вне кода</p>
    <h1 class="reveal" style="--delay:.06s">Хобби</h1>
    <p class="lead reveal" style="--delay:.12s">Интересы и занятия вне разработки — с фото и описанием.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($hobbies as $i => $item): ?>
        <div class="col-md-6 col-xl-4">
          <a class="hobby-card reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s" href="<?= e(page_url('hobbies', $item['slug'])) ?>">
            <div class="hobby-card-media">
              <?php if ($item['cover_image']): ?>
                <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="400" height="275">
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
        </div>
      <?php endforeach; ?>
      <?php if (!$hobbies): ?>
        <div class="col-12"><p class="empty-note">Записи появятся после добавления в админ-панели.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
