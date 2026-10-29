<section class="page-hero">
  <div class="container">
    <?= render_breadcrumbs($breadcrumbs) ?>
    <p class="eyebrow reveal">Квалификация</p>
    <h1 class="reveal" style="--delay:.06s">Сертификаты</h1>
    <p class="lead reveal" style="--delay:.12s">Документы о сдаче экзаменов и подтверждённых компетенциях.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($certificates as $i => $cert): ?>
        <div class="col-lg-6">
          <article class="cert-item reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s">
            <div class="cert-preview">
              <?php if ($cert['image_path']): ?>
                <a href="<?= e(upload_url($cert['image_path'])) ?>" target="_blank" rel="noopener">
                  <img src="<?= e(upload_url($cert['image_path'])) ?>" alt="<?= e($cert['title']) ?>" loading="lazy" width="480" height="360">
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
        </div>
      <?php endforeach; ?>
      <?php if (!$certificates): ?>
        <div class="col-12"><p class="empty-note">Добавьте грамоты через административное меню.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
