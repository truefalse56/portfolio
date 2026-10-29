<section class="section">
  <div class="container">
    <h1><?= e($heading ?? 'Не найдено') ?></h1>
    <p><a href="<?= e($backUrl ?? page_url('home')) ?>"><?= e($backLabel ?? 'На главную') ?></a></p>
  </div>
</section>
