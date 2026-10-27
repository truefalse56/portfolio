<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$projects = db()->query(
    'SELECT id, title, slug, short_description, stack, year, cover_image
     FROM projects WHERE is_published = 1
     ORDER BY sort_order ASC, id DESC LIMIT 3'
)->fetchAll();

$integrations = db()->query(
    'SELECT id, title, slug, category, short_description, cover_image
     FROM integrations WHERE is_published = 1
     ORDER BY sort_order ASC, id DESC LIMIT 4'
)->fetchAll();

$hobbies = db()->query(
    'SELECT id, title, slug, short_description, cover_image
     FROM hobbies WHERE is_published = 1
     ORDER BY sort_order ASC, id DESC LIMIT 3'
)->fetchAll();

$fullName = setting('full_name', 'Килин Вячеслав Александрович');
$title = setting('title', 'Fullstack Web Разработчик');
$subtitle = setting('subtitle', 'PHP · Bitrix Framework · Битрикс24 CRM');
$about = setting('about', '');
$ctaText = setting('hero_cta_text', 'Смотреть проекты');
$ctaLink = setting('hero_cta_link', 'projects.php');
if ($ctaLink && !str_starts_with($ctaLink, 'http') && !str_starts_with($ctaLink, '#')) {
    $ctaLink = base_url(ltrim($ctaLink, '/'));
}

require __DIR__ . '/templates/header.php';
?>

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
    <div class="hero-actions" data-animate="fade-up" data-delay="240">
      <a class="btn btn-primary" href="<?= e($ctaLink) ?>"><?= e($ctaText) ?></a>
      <a class="btn btn-ghost" href="<?= e(base_url('integrations.php')) ?>">Интеграции CRM</a>
    </div>
  </div>
</section>

<section class="section about-section">
  <div class="container about-grid">
    <div class="reveal">
      <p class="eyebrow">О разработчике</p>
      <h2>Решения на Bitrix,<br>которые работают в продакшене</h2>
    </div>
    <div class="reveal" style="--delay:.12s">
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
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">Проекты</p>
        <h2>Реализованные решения</h2>
      </div>
      <a class="text-link" href="<?= e(base_url('projects.php')) ?>">Все проекты</a>
    </div>
    <div class="work-list">
      <?php foreach ($projects as $i => $project): ?>
        <a class="work-row reveal" style="--delay:<?= e((string) ($i * 0.08)) ?>s" href="<?= e(base_url('project.php?slug=' . urlencode($project['slug']))) ?>">
          <div class="work-media">
            <?php if ($project['cover_image']): ?>
              <img src="<?= e(upload_url($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
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
      <a class="text-link" href="<?= e(base_url('integrations.php')) ?>">Все интеграции</a>
    </div>
    <div class="integration-grid">
      <?php foreach ($integrations as $i => $item): ?>
        <a class="integration-item reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s" href="<?= e(base_url('integration.php?slug=' . urlencode($item['slug']))) ?>">
          <span class="chip"><?= e($item['category']) ?></span>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['short_description']) ?></p>
          <span class="text-link">Подробнее</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($hobbies): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <p class="eyebrow">Вне кода</p>
        <h2>Хобби</h2>
      </div>
      <a class="text-link" href="<?= e(base_url('hobbies.php')) ?>">Все хобби</a>
    </div>
    <div class="hobby-grid">
      <?php foreach ($hobbies as $i => $item): ?>
        <a class="hobby-card reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s" href="<?= e(base_url('hobby.php?slug=' . urlencode($item['slug']))) ?>">
          <div class="hobby-card-media">
            <?php if ($item['cover_image']): ?>
              <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
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
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-tint">
  <div class="container cta-band reveal">
    <div>
      <p class="eyebrow">Документы</p>
      <h2>Сертификаты</h2>
      <p class="lead">Сертификаты о сдаче экзаменов и подтверждённые компетенции.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(base_url('certificates.php')) ?>">Открыть раздел</a>
  </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
