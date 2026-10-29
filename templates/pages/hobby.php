<article>
  <section class="page-hero">
    <div class="container narrow">
      <?= render_breadcrumbs($breadcrumbs) ?>
      <a class="text-link reveal" href="<?= e(page_url('hobbies')) ?>">← Все хобби</a>
      <h1 class="reveal" style="--delay:.05s"><?= e($item['title']) ?></h1>
      <?php if ($item['short_description']): ?>
        <p class="lead reveal" style="--delay:.1s"><?= e($item['short_description']) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($slides): ?>
    <section class="section" style="padding-top:0">
      <div class="container">
        <div
          id="hobbyCarousel"
          class="carousel slide hobby-carousel reveal<?= $hasCaptions ? ' has-captions' : '' ?>"
          data-bs-ride="carousel"
          data-bs-interval="6000"
          aria-label="<?= e($item['title']) ?>"
        >
          <?php if (count($slides) > 1): ?>
            <div class="carousel-indicators">
              <?php foreach ($slides as $i => $_): ?>
                <button
                  type="button"
                  data-bs-target="#hobbyCarousel"
                  data-bs-slide-to="<?= $i ?>"
                  class="<?= $i === 0 ? 'active' : '' ?>"
                  aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                  aria-label="Фото <?= $i + 1 ?>"
                ></button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="carousel-inner">
            <?php foreach ($slides as $i => $slide): ?>
              <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <img
                  src="<?= e(upload_url($slide['image_path'])) ?>"
                  alt="<?= e($slide['caption'] ?: $item['title']) ?>"
                  width="1200"
                  height="750"
                  class="d-block w-100"
                  <?= $i === 0 ? '' : 'loading="lazy"' ?>
                >
                <?php if (!empty($slide['caption'])): ?>
                  <div class="carousel-caption"><?= e($slide['caption']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (count($slides) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#hobbyCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Предыдущее фото</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#hobbyCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Следующее фото</span>
            </button>
            <div class="carousel-counter"><span data-carousel-current>1</span> / <?= count($slides) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="section" style="padding-top:<?= $slides ? '1rem' : '3rem' ?>">
    <div class="container narrow prose reveal">
      <?= markdown_lite($item['description']) ?>
    </div>
  </section>
</article>
