<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="stat"><span>Проекты</span><strong><?= $counts['projects'] ?></strong></div></div>
  <div class="col-6 col-xl-3"><div class="stat"><span>Интеграции</span><strong><?= $counts['integrations'] ?></strong></div></div>
  <div class="col-6 col-xl-3"><div class="stat"><span>Хобби</span><strong><?= $counts['hobbies'] ?></strong></div></div>
  <div class="col-6 col-xl-3"><div class="stat"><span>Статьи</span><strong><?= $counts['posts'] ?></strong></div></div>
  <div class="col-6 col-xl-3"><div class="stat"><span>Сертификаты</span><strong><?= $counts['certificates'] ?></strong></div></div>
</div>

<div class="panel">
  <h2>Быстрые действия</h2>
  <div class="d-flex flex-wrap gap-2 mt-3">
    <a class="btn btn-primary" href="<?= e(admin_url('projects/create')) ?>">+ Проект</a>
    <a class="btn btn-primary" href="<?= e(admin_url('integrations/create')) ?>">+ Интеграция</a>
    <a class="btn btn-primary" href="<?= e(admin_url('blog/create')) ?>">+ Статья</a>
    <a class="btn btn-primary" href="<?= e(admin_url('hobbies/create')) ?>">+ Хобби</a>
    <a class="btn btn-primary" href="<?= e(admin_url('certificates/create')) ?>">+ Сертификат</a>
    <a class="btn btn-ghost" href="<?= e(admin_url('settings')) ?>">Профиль и контакты</a>
  </div>
</div>
