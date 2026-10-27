-- Миграция: раздел «Хобби»
-- Можно выполнить вручную в phpMyAdmin, если автомиграция не сработала.

CREATE TABLE IF NOT EXISTS `hobbies` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hobby_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hobby_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_hobby_images_hobby` (`hobby_id`),
  CONSTRAINT `fk_hobby_images_hobby`
    FOREIGN KEY (`hobby_id`) REFERENCES `hobbies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
