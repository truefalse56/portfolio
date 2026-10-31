-- Schema only (вызывается из install.php). Encoding: utf8mb4

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `hobby_images`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `integration_screenshots`;
DROP TABLE IF EXISTS `project_screenshots`;
DROP TABLE IF EXISTS `hobbies`;
DROP TABLE IF EXISTS `integrations`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `certificates`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(120) NOT NULL DEFAULT 'Администратор',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('full_name', 'Килин Вячеслав Александрович'),
('title', 'Fullstack Web Разработчик'),
('subtitle', 'PHP · Bitrix Framework · Битрикс24 CRM'),
('about', 'Разрабатываю и внедряю решения на PHP и Bitrix Framework: от кастомизации публичной части до глубоких интеграций с Битрикс24 CRM — события, табы в карточках, валидация полей, REST и бизнес-процессы.'),
('email', 'hello@example.com'),
('telegram', ''),
('phone', ''),
('github', ''),
('location', 'Россия'),
('hero_cta_text', 'Смотреть проекты'),
('hero_cta_link', 'projects.php'),
('meta_description', 'Портфолио fullstack-разработчика PHP и Bitrix Framework. Интеграции Битрикс24 CRM, кастомизация и реализованные проекты.');

CREATE TABLE `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `short_description` VARCHAR(500) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `stack` VARCHAR(255) NOT NULL DEFAULT '',
  `role` VARCHAR(160) NOT NULL DEFAULT '',
  `year` VARCHAR(20) NOT NULL DEFAULT '',
  `cover_image` VARCHAR(255) NULL,
  `demo_url` VARCHAR(500) NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_projects_slug` (`slug`),
  KEY `idx_projects_published_sort` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `project_screenshots` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_project_screenshots_project` (`project_id`),
  CONSTRAINT `fk_project_screenshots_project`
    FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `integrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `category` VARCHAR(120) NOT NULL DEFAULT 'Битрикс24 CRM',
  `short_description` VARCHAR(500) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `features` TEXT NULL,
  `code_example` MEDIUMTEXT NULL,
  `code_language` VARCHAR(40) NOT NULL DEFAULT 'php',
  `cover_image` VARCHAR(255) NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_integrations_slug` (`slug`),
  KEY `idx_integrations_published_sort` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `integration_screenshots` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `integration_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_integration_screenshots_integration` (`integration_id`),
  CONSTRAINT `fk_integration_screenshots_integration`
    FOREIGN KEY (`integration_id`) REFERENCES `integrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `issuer` VARCHAR(200) NOT NULL DEFAULT '',
  `issued_at` DATE NULL,
  `description` TEXT NULL,
  `image_path` VARCHAR(255) NULL,
  `file_path` VARCHAR(255) NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_certificates_published_sort` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hobbies` (
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

CREATE TABLE `hobby_images` (
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

CREATE TABLE `posts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects`
(`title`, `slug`, `short_description`, `description`, `stack`, `role`, `year`, `is_published`, `sort_order`)
VALUES
(
  'Корпоративный портал на Bitrix Framework',
  'corporate-portal-bitrix',
  'Внутренний портал компании: новости, документы, заявки и интеграции с CRM.',
  'Спроектирован и реализован корпоративный портал на Bitrix Framework: кастомные компоненты, права доступа, интеграции с Битрикс24 и внешними сервисами. Оптимизирована производительность и UX публичной и служебной частей.',
  'PHP, Bitrix Framework, MySQL, JS',
  'Fullstack-разработчик',
  '2024',
  1,
  10
),
(
  'Интернет-магазин с CRM-синхронизацией',
  'ecommerce-crm-sync',
  'Каталог, заказы и двусторонняя синхронизация статусов с Битрикс24.',
  'Реализован магазин с витриной на Bitrix и синхронизацией заказов, клиентов и статусов оплаты с Битрикс24 CRM. Добавлены вебхуки, очереди обработки и админ-отчёты.',
  'PHP, Bitrix, Битрикс24 REST, MySQL',
  'Backend / интеграции',
  '2025',
  1,
  20
);

INSERT INTO `integrations`
(`title`, `slug`, `category`, `short_description`, `description`, `features`, `code_example`, `code_language`, `is_published`, `sort_order`)
VALUES
(
  'Обработчики событий CRM',
  'crm-event-handlers',
  'Битрикс24 CRM',
  'Встраивание в события CRM: OnAfterCrmDealAdd, OnBeforeCrmCompanyUpdate и др.',
  'Регистрация серверных обработчиков событий Битрикс24 для автоматизации бизнес-логики: проверка данных при сохранении, постановка задач, уведомления и синхронизация со внешними системами.',
  '["Регистрация через EventManager","Проверка и модификация полей","Асинхронные уведомления","Логирование изменений"]',
  '<?php\n\nuse Bitrix\\Main\\EventManager;\n\n$eventManager = EventManager::getInstance();\n\n$eventManager->addEventHandler(\n    ''crm'',\n    ''OnAfterCrmDealAdd'',\n    [''\\\\Local\\\\Crm\\\\DealHandler'', ''onAfterAdd'']\n);\n\nclass DealHandler\n{\n    public static function onAfterAdd(array &$fields): void\n    {\n        if (empty($fields[''ID''])) {\n            return;\n        }\n\n        \\CCrmActivity::Add([\n            ''TYPE_ID''   => \\CCrmActivityType::Task,\n            ''SUBJECT''   => ''Обработать новую сделку #'' . $fields[''ID''],\n            ''COMPLETED'' => ''N'',\n            ''RESPONSIBLE_ID'' => (int)($fields[''ASSIGNED_BY_ID''] ?? 1),\n            ''OWNER_ID''  => (int)$fields[''ID''],\n            ''OWNER_TYPE_ID'' => \\CCrmOwnerType::Deal,\n        ]);\n    }\n}',
  'php',
  1,
  10
),
(
  'Кастомизация публичной части',
  'public-part-customization',
  'Битрикс24 / Bitrix',
  'Кастомные шаблоны, компоненты и виджеты для публичных страниц и портала.',
  'Адаптация публичной части: собственные шаблоны сайта, компоненты списка/деталки, AJAX-виджеты, стилизация под бренд и интеграция данных CRM на фронтенде.',
  '["Кастомные компоненты Bitrix","Шаблоны и темы","AJAX-виджеты","Адаптивная вёрстка"]',
  '<?php\n\nif (!defined(''B_PROLOG_INCLUDED'') || B_PROLOG_INCLUDED !== true) {\n    die();\n}\n\n$this->setFrameMode(true);\n?>\n<div class="crm-public-widget">\n    <h2><?= htmlspecialcharsbx($arResult[''TITLE'']) ?></h2>\n    <ul class="crm-public-widget__list">\n        <?php foreach ($arResult[''ITEMS''] as $item): ?>\n            <li>\n                <strong><?= htmlspecialcharsbx($item[''TITLE'']) ?></strong>\n                <span><?= htmlspecialcharsbx($item[''STATUS'']) ?></span>\n            </li>\n        <?php endforeach; ?>\n    </ul>\n</div>',
  'php',
  1,
  20
),
(
  'Табы в детальной карточке компании',
  'company-detail-tabs',
  'Битрикс24 CRM',
  'Встраивание кастомных вкладок внутрь карточки компании CRM.',
  'Добавление собственных табов в детальную карточку компании: связанные данные из внешних систем, история интеграций, кастомные формы и отчёты прямо в интерфейсе CRM.',
  '["Placement CRM_COMPANY_DETAIL_TAB","REST-приложение","Кастомный UI внутри CRM","Права доступа"]',
  '<?php\n\n$result = CRest::call(''placement.bind'', [\n    ''PLACEMENT'' => ''CRM_COMPANY_DETAIL_TAB'',\n    ''HANDLER''   => ''https://example.com/apps/company-tab/'',\n    ''TITLE''     => ''Интеграции'',\n]);\n\n$options   = json_decode($_REQUEST[''PLACEMENT_OPTIONS''] ?? ''{}'', true) ?: [];\n$companyId = (int)($options[''ID''] ?? 0);\n$company = CRest::call(''crm.company.get'', [''id'' => $companyId]);\n\necho htmlspecialchars($company[''result''][''TITLE''] ?? '''');',
  'php',
  1,
  30
),
(
  'Валидация полей CRM',
  'crm-field-validation',
  'Битрикс24 CRM',
  'Серверная и клиентская проверка полей сделок, компаний и лидов.',
  'Реализация строгой валидации обязательных и зависимых полей: блокировка сохранения при ошибках, подсказки пользователю, проверка ИНН/телефона/email и бизнес-правил.',
  '["OnBeforeCrm*Add/Update","Кастомные правила","Сообщения об ошибках","Зависимые поля"]',
  '<?php\n\nuse Bitrix\\Main\\EventManager;\n\nEventManager::getInstance()->addEventHandler(\n    ''crm'',\n    ''OnBeforeCrmCompanyUpdate'',\n    static function (array &$fields) {\n        if (isset($fields[''UF_INN'']) && $fields[''UF_INN''] !== '''') {\n            $inn = preg_replace(''/\\D+/'', '''', (string)$fields[''UF_INN'']);\n            if (!in_array(strlen($inn), [10, 12], true)) {\n                $GLOBALS[''APPLICATION'']->ThrowException(''ИНН должен содержать 10 или 12 цифр.'');\n                return false;\n            }\n            $fields[''UF_INN''] = $inn;\n        }\n\n        if (empty($fields[''PHONE'']) && empty($fields[''EMAIL''])) {\n            $GLOBALS[''APPLICATION'']->ThrowException(''Укажите телефон или email компании.'');\n            return false;\n        }\n\n        return true;\n    }\n);',
  'php',
  1,
  40
);

INSERT INTO `certificates`
(`title`, `issuer`, `issued_at`, `description`, `is_published`, `sort_order`)
VALUES
(
  'Сертифицированный разработчик Bitrix Framework',
  '1С-Битрикс',
  '2024-06-01',
  'Экзамен по разработке на Bitrix Framework. Замените карточку и загрузите скан грамоты через админку.',
  1,
  10
),
(
  'Битрикс24: интеграция и автоматизация',
  '1С-Битрикс',
  '2025-02-15',
  'Подтверждение компетенций по REST, событиям и кастомизации CRM. Добавьте свой диплом в админ-панели.',
  1,
  20
);
