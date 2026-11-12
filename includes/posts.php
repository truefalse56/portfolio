<?php

declare(strict_types=1);

/**
 * Гарантирует наличие posts после восстановления БД из бэкапа.
 * Только CREATE IF NOT EXISTS — существующие данные не трогает.
 */
function ensure_posts_table(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        db()->exec(
            "CREATE TABLE IF NOT EXISTS `posts` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(200) NOT NULL,
              `slug` VARCHAR(220) NOT NULL,
              `excerpt` VARCHAR(500) NOT NULL DEFAULT '',
              `content` MEDIUMTEXT NOT NULL,
              `cover_image` VARCHAR(255) NULL,
              `published_at` DATETIME NULL,
              `is_published` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_posts_slug` (`slug`),
              KEY `idx_posts_published_date` (`is_published`, `published_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable) {
        // сайт не должен падать, если CREATE временно недоступен
    }
}

/**
 * @return list<array<string, mixed>>
 */
function posts_fetch_all(string $sql, array $params = []): array
{
    ensure_posts_table();

    try {
        if ($params === []) {
            return db()->query($sql)->fetchAll();
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * @return array<string, mixed>|null
 */
function posts_fetch_one(string $sql, array $params = []): ?array
{
    ensure_posts_table();

    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ?: null;
    } catch (Throwable) {
        return null;
    }
}

function posts_count(): int
{
    ensure_posts_table();

    try {
        return (int) db()->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

/**
 * Если после бэкапа таблица пустая — подтянуть статьи из SQL-сида.
 * Существующие записи не трогает (проверка count + INSERT IGNORE).
 */
function ensure_posts_seeded(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    ensure_posts_table();

    try {
        if (posts_count() > 0) {
            return;
        }

        $seed = base_path('sql/restore_posts_bitrix24.sql');
        if (!is_file($seed)) {
            return;
        }

        $sql = file_get_contents($seed);
        if ($sql !== false && $sql !== '') {
            db()->exec($sql);
        }
    } catch (Throwable) {
        // не валим сайт, статьи можно залить вручную через phpMyAdmin
    }
}
