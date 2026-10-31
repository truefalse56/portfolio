<?php

declare(strict_types=1);

$fullName = setting('full_name', 'Килин Вячеслав Александрович');
$email = setting('email', '');
$telegram = setting('telegram', '');
$phone = setting('phone', '');
$github = setting('github', '');
$location = setting('location', '');
?>
  </main>

  <footer class="site-footer" id="contact">
    <div class="container">
      <div class="row g-4 mb-5">
        <div class="col-lg-7 reveal">
          <p class="eyebrow">Контакты</p>
          <h2><?= e($fullName) ?></h2>
          <p class="lead mb-0"><?= e(setting('title', 'Fullstack Web Разработчик')) ?>. Готов обсудить проект, интеграцию или доработку на Bitrix / Битрикс24.</p>
        </div>
        <div class="col-lg-5 reveal" style="--delay:.1s">
          <div class="footer-links d-flex flex-column align-items-start gap-2">
            <?php if ($email): ?>
              <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
            <?php endif; ?>
            <?php if ($phone): ?>
              <a href="tel:<?= e(preg_replace('/\s+/', '', $phone) ?? $phone) ?>"><?= e($phone) ?></a>
            <?php endif; ?>
            <?php if ($telegram): ?>
              <a href="<?= e(str_starts_with($telegram, 'http') ? $telegram : 'https://t.me/' . ltrim($telegram, '@')) ?>" target="_blank" rel="noopener">Telegram</a>
            <?php endif; ?>
            <?php if ($github): ?>
              <a href="<?= e($github) ?>" target="_blank" rel="noopener">GitHub</a>
            <?php endif; ?>
            <?php if ($location): ?>
              <span><?= e($location) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="footer-bottom d-flex justify-content-between flex-wrap gap-2">
        <span>© <?= date('Y') ?> <?= e($fullName) ?></span>
        <span>PHP · Bitrix Framework · Битрикс24</span>
      </div>
    </div>
  </footer>

  <script src="<?= e(asset('vendor/bootstrap/bootstrap.bundle.min.js')) ?>" defer></script>
  <?php if (!empty($loadCodeHighlight)): ?>
  <script src="<?= e(asset('vendor/prism/prism-core.min.js')) ?>" defer></script>
  <script src="<?= e(asset('vendor/prism/prism-autoloader.min.js')) ?>" defer></script>
  <?php endif; ?>
  <script src="<?= e(asset('js/main.js')) ?>" defer></script>
<!-- Yandex.Metrika counter -->
<script>
  window.addEventListener('load', function () {
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=113839848', 'ym');

    ym(113839848, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
  });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/113839848" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</body>
</html>
