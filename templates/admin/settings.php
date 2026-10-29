<form class="panel mb-4" method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="profile">
  <h2>Публичный профиль</h2>
  <div class="row g-3 mt-1">
    <div class="col-md-6">
      <label class="form-label">ФИО</label>
      <input class="form-control" type="text" name="full_name" value="<?= e($values['full_name']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Должность / роль</label>
      <input class="form-control" type="text" name="title" value="<?= e($values['title']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Подзаголовок</label>
      <input class="form-control" type="text" name="subtitle" value="<?= e($values['subtitle']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">О себе</label>
      <textarea class="form-control" name="about"><?= e($values['about']) ?></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">Email</label>
      <input class="form-control" type="email" name="email" value="<?= e($values['email']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Телефон</label>
      <input class="form-control" type="text" name="phone" value="<?= e($values['phone']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Telegram</label>
      <input class="form-control" type="text" name="telegram" value="<?= e($values['telegram']) ?>" placeholder="@username или ссылка">
    </div>
    <div class="col-md-6">
      <label class="form-label">GitHub</label>
      <input class="form-control" type="url" name="github" value="<?= e($values['github']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Локация</label>
      <input class="form-control" type="text" name="location" value="<?= e($values['location']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Текст кнопки в hero</label>
      <input class="form-control" type="text" name="hero_cta_text" value="<?= e($values['hero_cta_text']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Ссылка кнопки hero</label>
      <input class="form-control" type="text" name="hero_cta_link" value="<?= e($values['hero_cta_link']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Meta description</label>
      <input class="form-control" type="text" name="meta_description" value="<?= e($values['meta_description']) ?>">
    </div>
  </div>
  <div class="mt-4">
    <button class="btn btn-primary" type="submit">Сохранить профиль</button>
  </div>
</form>

<form class="panel" method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="section" value="password">
  <h2>Смена пароля</h2>
  <div class="row g-3 mt-1">
    <div class="col-12">
      <label class="form-label">Текущий пароль</label>
      <input class="form-control" type="password" name="current_password" required autocomplete="current-password">
    </div>
    <div class="col-md-6">
      <label class="form-label">Новый пароль</label>
      <input class="form-control" type="password" name="new_password" required minlength="6" autocomplete="new-password">
    </div>
    <div class="col-md-6">
      <label class="form-label">Подтверждение</label>
      <input class="form-control" type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
    </div>
  </div>
  <div class="mt-4">
    <button class="btn btn-primary" type="submit">Обновить пароль</button>
  </div>
</form>
