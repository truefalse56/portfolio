<?php

declare(strict_types=1);

/**
 * Лёгкие миграции для уже установленного сайта.
 * Ошибки глотаются — сайт не должен падать из‑за миграции.
 */
function run_migrations(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        $pdo = db();

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `hobbies` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(200) NOT NULL,
              `slug` VARCHAR(220) NOT NULL,
              `short_description` VARCHAR(500) NOT NULL DEFAULT '',
              `description` TEXT NOT NULL,
              `cover_image` VARCHAR(255) NULL,
              `is_published` TINYINT(1) NOT NULL DEFAULT 1,
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_hobbies_slug` (`slug`),
              KEY `idx_hobbies_published_sort` (`is_published`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `hobby_images` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `hobby_id` INT UNSIGNED NOT NULL,
              `image_path` VARCHAR(255) NOT NULL,
              `caption` VARCHAR(255) NOT NULL DEFAULT '',
              `sort_order` INT NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              KEY `idx_hobby_images_hobby` (`hobby_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // FK добавляем отдельно — на части хостингов повторный CREATE с CONSTRAINT падает
        $fk = $pdo->query(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'hobby_images'
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'
               AND CONSTRAINT_NAME = 'fk_hobby_images_hobby'
             LIMIT 1"
        )->fetchColumn();

        if (!$fk) {
            try {
                $pdo->exec(
                    'ALTER TABLE `hobby_images`
                     ADD CONSTRAINT `fk_hobby_images_hobby`
                     FOREIGN KEY (`hobby_id`) REFERENCES `hobbies` (`id`) ON DELETE CASCADE'
                );
            } catch (Throwable) {
                // без FK сайт всё равно работает
            }
        }

        $pdo->exec(
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
        // не валим сайт
    }
}
