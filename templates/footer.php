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
    <div class="container footer-grid">
      <div class="footer-brand reveal">
        <p class="eyebrow">Контакты</p>
        <h2><?= e($fullName) ?></h2>
        <p class="lead"><?= e(setting('title', 'Fullstack Web Разработчик')) ?>. Готов обсудить проект, интеграцию или доработку на Bitrix / Битрикс24.</p>
      </div>
      <div class="footer-links reveal" style="--delay:.1s">
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
    <div class="container footer-bottom">
      <span>© <?= date('Y') ?> <?= e($fullName) ?></span>
      <span>PHP · Bitrix Framework · Битрикс24</span>
    </div>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
  <script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
