<section class="page-hero">
  <div class="container">
    <?= render_breadcrumbs($breadcrumbs) ?>
    <p class="eyebrow reveal">Портфолио</p>
    <h1 class="reveal" style="--delay:.06s">Реализованные проекты</h1>
    <p class="lead reveal" style="--delay:.12s">Кейсы на PHP, Bitrix Framework и Битрикс24 — от порталов до CRM-интеграций.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="work-list">
      <?php foreach ($projects as $i => $project): ?>
        <a class="work-row reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s" href="<?= e(page_url('projects', $project['slug'])) ?>">
          <div class="work-media">
            <?php if ($project['cover_image']): ?>
              <img src="<?= e(upload_url($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>" loading="lazy" width="360" height="270">
            <?php else: ?>
              <div class="media-placeholder" data-label="<?= e(mb_substr($project['title'], 0, 1)) ?>"></div>
            <?php endif; ?>
          </div>
          <div class="work-body">
            <div class="work-meta">
              <?php if ($project['year']): ?><span><?= e($project['year']) ?></span><?php endif; ?>
              <?php if ($project['role']): ?><span><?= e($project['role']) ?></span><?php endif; ?>
              <?php if ($project['stack']): ?><span><?= e($project['stack']) ?></span><?php endif; ?>
            </div>
            <h3><?= e($project['title']) ?></h3>
            <p><?= e($project['short_description']) ?></p>
          </div>
          <span class="work-arrow" aria-hidden="true">→</span>
        </a>
      <?php endforeach; ?>
      <?php if (!$projects): ?>
        <p class="empty-note">Пока нет опубликованных проектов.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
