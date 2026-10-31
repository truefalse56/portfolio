<section class="page-hero">
  <div class="container">
    <?= render_breadcrumbs($breadcrumbs) ?>
    <p class="eyebrow reveal">Заметки</p>
    <h1 class="reveal" style="--delay:.06s">Блог</h1>
    <p class="lead reveal" style="--delay:.12s">Определения из разработки и короткие примеры кода.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($posts as $i => $item): ?>
        <?php
          $cardExcerpt = $item['excerpt'] !== ''
              ? $item['excerpt']
              : article_excerpt((string) $item['content'], 180);
        ?>
        <div class="col-md-6 col-xl-4">
          <a class="blog-card reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s" href="<?= e(page_url('blog', $item['slug'])) ?>">
            <?php if ($item['cover_image']): ?>
              <div class="blog-card-media">
                <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="640" height="360">
              </div>
            <?php endif; ?>
            <div class="blog-card-body">
              <time datetime="<?= e(post_date_iso($item)) ?>"><?= e(post_date($item)) ?></time>
              <h3><?= e($item['title']) ?></h3>
              <p><?= e($cardExcerpt) ?></p>
              <span class="text-link">Читать</span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if (!$posts): ?>
        <div class="col-12"><p class="empty-note">Статьи появятся после добавления в админ-панели.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
