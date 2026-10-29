# Портфолио разработчика — Килин Вячеслав Александрович

Сайт-визитка fullstack-разработчика **PHP / Bitrix Framework / Битрикс24 CRM** с административной панелью.

## Стек

- PHP 8.1+ (рекомендуется 8.3)
- MySQL
- [Slim Framework 4](https://www.slimframework.com/)
- Bootstrap 5.3
- Composer

## Возможности

**Публичная часть**
- Главная с ФИО, должностью и акцентом на Битрикс24
- Проекты, интеграции CRM, хобби (карусель), сертификаты
- SEO: canonical, Open Graph, JSON-LD, sitemap.xml, robots.txt
- Светлая / тёмная тема

**Админка** (`/admin/login`)
- CRUD проектов, интеграций, хобби, грамот
- Загрузка обложек и скриншотов
- Настройки профиля и смена пароля
- Физической папки `admin/` нет — маршруты обрабатывает Slim

## Деплой на shared-хостинг

1. Залейте файлы **включая** каталог `vendor/` (или выполните `composer install --no-dev` локально и залейте результат).
2. Убедитесь, что `assets/uploads` и `storage` доступны для записи.
3. Настройте MySQL в `config/database.local.php` (не перезаписывайте его при деплое). При сбое БД откройте `fix-database.php`, затем удалите его.
4. Document root должен указывать на корень проекта (где лежит `index.php`). Нужен `mod_rewrite`.
5. Войдите в `/admin/login`.

## Локальная разработка

```bash
composer install
# OSPanel / Apache с document root на эту папку
# или: php -S localhost:8080 -t . index.php
```

## Структура

```
├── app/                 # Контроллеры, роуты, middleware (Slim)
├── assets/              # CSS, JS, uploads
├── config/              # Настройки приложения и БД
├── includes/            # helpers, db, auth, seo, upload
├── templates/           # Публичные и admin-шаблоны
├── vendor/              # Composer-зависимости
├── index.php            # Front controller (Slim)
├── composer.json
└── .htaccess            # Rewrite → index.php
```

## Безопасность

- Пароли через `password_hash`
- CSRF в админ-формах
- Каталоги `app`, `config`, `includes`, `sql`, `storage`, `templates`, `vendor` закрыты через `.htaccess`
- В `assets/uploads` запрещено выполнение PHP
