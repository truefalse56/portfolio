<article>
  <section class="page-hero">
    <div class="container narrow">
      <?= render_breadcrumbs($breadcrumbs) ?>
      <a class="text-link reveal" href="<?= e(page_url('blog')) ?>">← Все статьи</a>
      <p class="article-date reveal" style="--delay:.04s">
        <time datetime="<?= e(post_date_iso($item)) ?>"><?= e(post_date($item)) ?></time>
      </p>
      <h1 class="reveal" style="--delay:.08s"><?= e($item['title']) ?></h1>
    </div>
  </section>

  <?php if ($item['cover_image']): ?>
    <section class="bleed-media reveal">
      <img src="<?= e(upload_url($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>" width="1200" height="514">
    </section>
  <?php endif; ?>

  <section class="section" style="padding-top:<?= $item['cover_image'] ? '3rem' : '1.5rem' ?>">
    <div class="container narrow prose reveal">
      <?= render_article((string) $item['content']) ?>
    </div>
  </section>
</article>
