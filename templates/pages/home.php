<section class="hero">
  <div class="hero-bg" aria-hidden="true">
    <div class="hero-orb hero-orb--a"></div>
    <div class="hero-orb hero-orb--b"></div>
    <div class="hero-grid"></div>
  </div>
  <div class="container hero-content">
    <p class="hero-brand" data-animate="fade-up"><?= e($fullName) ?></p>
    <h1 class="hero-title" data-animate="fade-up" data-delay="80"><?= e($title) ?></h1>
    <p class="hero-lead" data-animate="fade-up" data-delay="160"><?= e($subtitle) ?>. Специализация — интеграции Битрикс24 CRM, кастомизация и серверная логика на PHP.</p>
    <div class="hero-actions d-flex flex-wrap gap-3" data-animate="fade-up" data-delay="240">
      <a class="btn btn-primary" href="<?= e($ctaLink) ?>"><?= e($ctaText) ?></a>
      <a class="btn btn-ghost" href="<?= e(page_url('integrations')) ?>">Интеграции CRM</a>
    </div>
  </div>
</section>

<section class="section about-section" id="about">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-5 reveal">
        <p class="eyebrow">О разработчике</p>
        <h2>Решения на Bitrix,<br>которые работают в продакшене</h2>
      </div>
      <div class="col-lg-7 reveal" style="--delay:.12s">
        <p class="lead"><?= nl2br(e($about)) ?></p>
        <ul class="skill-list">
          <li>PHP 8.x / Bitrix Framework</li>
          <li>Битрикс24 CRM: события, REST, placements</li>
          <li>Кастомизация публичной части</li>
          <li>Валидация полей и бизнес-правила</li>
          <li>MySQL, JavaScript, API-интеграции</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">Проекты</p>
        <h2>Реализованные решения</h2>
      </div>
      <a class="text-link" href="<?= e(page_url('projects')) ?>">Все проекты</a>
    </div>
    <div class="work-list">
      <?php foreach ($projects as $i => $project): ?>
        <a class="work-row reveal" style="--delay:<?= e((string) ($i * 0.08)) ?>s" href="<?= e(page_url('projects', $project['slug'])) ?>">
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
              <?php if ($project['stack']): ?><span><?= e($project['stack']) ?></span><?php endif; ?>
            </div>
            <h3><?= e($project['title']) ?></h3>
            <p><?= e($project['short_description']) ?></p>
          </div>
          <span class="work-arrow" aria-hidden="true">→</span>
        </a>
      <?php endforeach; ?>
      <?php if (!$projects): ?>
        <p class="empty-note">Проекты появятся после добавления в админ-панели.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-tint">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">Битрикс24 CRM</p>
        <h2>Интеграции и кастомизация</h2>
      </div>
      <a class="text-link" href="<?= e(page_url('integrations')) ?>">Все интеграции</a>
    </div>
    <div class="row g-4">
      <?php foreach ($integrations as $i => $item): ?>
        <div class="col-md-6">
          <a class="integration-item reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s" href="<?= e(page_url('integrations', $item['slug'])) ?>">
            <span class="chip"><?= e($item['category']) ?></span>
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['short_description']) ?></p>
            <span class="text-link">Подробнее</span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($posts): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">Блог</p>
        <h2>Заметки и определения</h2>
      </div>
      <a class="text-link" href="<?= e(page_url('blog')) ?>">Все статьи</a>
    </div>
    <div class="row g-4">
      <?php foreach ($posts as $i => $item): ?>
        <?php
          $cardExcerpt = $item['excerpt'] !== ''
              ? $item['excerpt']
              : article_excerpt((string) $item['content'], 180);
        ?>
        <div class="col-md-6 col-xl-4">
          <a class="blog-card reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s" href="<?= e(page_url('blog', $item['slug'])) ?>">
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
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($hobbies): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">Вне кода</p>
        <h2>Хобби</h2>
      </div>
      <a class="text-link" href="<?= e(page_url('hobbies')) ?>">Все хобби</a>
    </div>
    <div class="row g-4">
      <?php foreach ($hobbies as $i => $item): ?>
        <div class="col-md-6 col-xl-4">
          <a class="hobby-card reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s" href="<?= e(page_url('hobbies', $item['slug'])) ?>">
            <div class="hobby-card-media">
              <?php if ($item['cover_image']): ?>
                <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="400" height="275">
              <?php else: ?>
                <div class="media-placeholder" data-label="<?= e(mb_substr($item['title'], 0, 1)) ?>"></div>
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
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-tint">
  <div class="container">
    <div class="cta-band reveal">
      <div>
        <p class="eyebrow">Документы</p>
        <h2>Сертификаты</h2>
        <p class="lead mb-0">Сертификаты о сдаче экзаменов и подтверждённые компетенции.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(page_url('certificates')) ?>">Открыть раздел</a>
    </div>
  </div>
</section>
