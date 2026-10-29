<section class="page-hero">
  <div class="container">
    <?= render_breadcrumbs($breadcrumbs) ?>
    <p class="eyebrow reveal">Битрикс24 CRM</p>
    <h1 class="reveal" style="--delay:.06s">Интеграции и кастомизация</h1>
    <p class="lead reveal" style="--delay:.12s">События CRM, табы в карточках, валидация полей, публичная часть и примеры кода.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($integrations as $i => $item): ?>
        <div class="col-md-6">
          <a class="integration-item reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s" href="<?= e(page_url('integrations', $item['slug'])) ?>">
            <?php if ($item['cover_image']): ?>
              <div class="integration-cover">
                <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="640" height="320">
              </div>
            <?php endif; ?>
            <span class="chip"><?= e($item['category']) ?></span>
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['short_description']) ?></p>
            <span class="text-link">Смотреть решение</span>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if (!$integrations): ?>
        <div class="col-12"><p class="empty-note">Интеграции ещё не добавлены.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
