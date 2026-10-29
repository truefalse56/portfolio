<article itemscope itemtype="https://schema.org/CreativeWork">
  <section class="page-hero">
    <div class="container narrow">
      <?= render_breadcrumbs($breadcrumbs) ?>
      <a class="text-link reveal" href="<?= e(page_url('projects')) ?>">← Все проекты</a>
      <h1 class="reveal" style="--delay:.05s" itemprop="name"><?= e($project['title']) ?></h1>
      <p class="lead reveal" style="--delay:.1s" itemprop="description"><?= e($project['short_description']) ?></p>
      <div class="meta-row reveal" style="--delay:.15s">
        <?php if ($project['year']): ?><span><?= e($project['year']) ?></span><?php endif; ?>
        <?php if ($project['role']): ?><span><?= e($project['role']) ?></span><?php endif; ?>
        <?php if ($project['stack']): ?><span itemprop="keywords"><?= e($project['stack']) ?></span><?php endif; ?>
      </div>
      <?php if ($project['demo_url']): ?>
        <p class="reveal" style="--delay:.2s"><a class="btn btn-primary" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener">Открыть проект</a></p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($project['cover_image']): ?>
    <section class="bleed-media reveal">
      <img src="<?= e(upload_url($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>" itemprop="image" width="1200" height="514">
    </section>
  <?php endif; ?>

  <section class="section">
    <div class="container narrow prose reveal" itemprop="text">
      <?= markdown_lite($project['description']) ?>
    </div>
  </section>

  <?php if ($screenshots): ?>
    <section class="section section-tint">
      <div class="container">
        <h2 class="reveal">Скриншоты</h2>
        <div class="row g-4 mt-1">
          <?php foreach ($screenshots as $i => $shot): ?>
            <div class="col-md-6">
              <figure class="shot reveal" style="--delay:<?= e((string) ($i * 0.06)) ?>s">
                <a href="<?= e(upload_url($shot['image_path'])) ?>" target="_blank" rel="noopener">
                  <img src="<?= e(upload_url($shot['image_path'])) ?>" alt="<?= e($shot['caption'] ?: $project['title']) ?>" loading="lazy" width="800" height="500">
                </a>
                <?php if ($shot['caption']): ?>
                  <figcaption><?= e($shot['caption']) ?></figcaption>
                <?php endif; ?>
              </figure>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</article>
