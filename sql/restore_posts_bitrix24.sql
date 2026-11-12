-- Восстановление блога после бэкапа.
-- Создаёт posts при отсутствии и добавляет 30 статей про Bitrix24.
-- Существующие таблицы/записи не удаляет. Повторный импорт безопасен (INSERT IGNORE).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
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

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('EventManager: регистрация обработчиков событий', 'eventmanager-obrabotchiki-sobytij', 'EventManager подписывает код на события ядра и модулей Bitrix: добавление сделки, изменение компании, сохранение элемента.', '**EventManager** — точка подписки на события Bitrix Framework и Битрикс24.\n\n```php\n<?php\n\nuse Bitrix\\Main\\EventManager;\n\n$eventManager = EventManager::getInstance();\n$eventManager->addEventHandler(\n    \'crm\',\n    \'OnAfterCrmDealAdd\',\n    [\'\\\\Local\\\\Crm\\\\DealHandler\', \'onAfterAdd\']\n);\n```\n\nРегистрацию обычно делают в `init.php` модуля или в `/local/php_interface/init.php`. Обработчик должен быть быстрым: тяжёлую работу лучше уводить в агент или очередь.', NULL, '2026-10-14 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('OnBefore и OnAfter в CRM', 'onbefore-onafter-crm', 'OnBefore срабатывает до записи и может её остановить. OnAfter — после успешного сохранения, когда ID уже есть.', '## OnBefore\n\nПодходит для валидации и нормализации полей.\n\n```php\n<?php\n\nuse Bitrix\\Main\\EventManager;\n\nEventManager::getInstance()->addEventHandler(\n    \'crm\',\n    \'OnBeforeCrmCompanyUpdate\',\n    static function (array &$fields) {\n        if (isset($fields[\'UF_INN\'])) {\n            $inn = preg_replace(\'/\\D+/\', \'\', (string) $fields[\'UF_INN\']);\n            if ($inn !== \'\' && !in_array(strlen($inn), [10, 12], true)) {\n                $GLOBALS[\'APPLICATION\']->ThrowException(\'ИНН должен содержать 10 или 12 цифр.\');\n                return false;\n            }\n            $fields[\'UF_INN\'] = $inn;\n        }\n        return true;\n    }\n);\n```\n\n## OnAfter\n\nКогда сущность уже сохранена и нужен побочный эффект: задача, уведомление, внешний API.', NULL, '2026-10-15 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('REST Битрикс24 через входящий webhook', 'rest-bitrix24-webhook', 'Входящий webhook даёт URL для вызова методов REST CRM из внешнего PHP-сервиса или локального скрипта.', '```php\n<?php\n\n$webhook = \'https://example.bitrix24.ru/rest/1/xxxxxxxx/\';\n$method = \'crm.deal.get\';\n$query = http_build_query([\'id\' => 42]);\n$response = file_get_contents($webhook . $method . \'.json?\' . $query);\n$data = json_decode((string) $response, true);\n$deal = $data[\'result\'] ?? null;\n```\n\nХраните webhook только на сервере. Проверяйте `error` в ответе. Для пачки операций используйте `batch`.', NULL, '2026-10-16 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('batch в REST Битрикс24', 'rest-batch-bitrix24', 'Метод batch выполняет несколько REST-команд одним HTTP-запросом и снижает накладные расходы сети.', '```php\n<?php\n\n$cmd = [\n    \'deal\' => \'crm.deal.get?id=42\',\n    \'company\' => \'crm.company.get?id=$result[deal][COMPANY_ID]\',\n];\n\n$payload = [\n    \'halt\' => 0,\n    \'cmd\' => $cmd,\n];\n```\n\nУчитывайте лимит команд, зависимости через `$result[...]` и флаг `halt`.', NULL, '2026-10-17 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Placement: вкладка в карточке сделки', 'placement-vkladka-sdelki', 'Placement встраивает приложение в интерфейс CRM — например, отдельный таб в детальной карточке сделки.', '```php\n<?php\n\n$result = CRest::call(\'placement.bind\', [\n    \'PLACEMENT\' => \'CRM_DEAL_DETAIL_TAB\',\n    \'HANDLER\' => \'https://example.com/apps/deal-tab/\',\n    \'TITLE\' => \'Интеграции\',\n]);\n\n$options = json_decode($_REQUEST[\'PLACEMENT_OPTIONS\'] ?? \'{}\', true) ?: [];\n$dealId = (int) ($options[\'ID\'] ?? 0);\n```\n\nВ обработчике можно показать связанные данные внешней системы или форму запуска процесса.', NULL, '2026-10-18 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Агенты Bitrix для фоновых задач', 'agenty-bitrix', 'Агент — PHP-функция по расписанию. Удобно для синхронизаций, очисток и повторных попыток API.', '```php\n<?php\n\nfunction local_sync_deals_agent(): string\n{\n    \\Local\\Crm\\DealSync::run();\n    return \'local_sync_deals_agent();\';\n}\n\n\\CAgent::AddAgent(\'local_sync_deals_agent();\', \'\', \'N\', 3600);\n```\n\nАгент должен быть идемпотентным. Длинные операции дробите на пачки. На проде предпочтителен cron.', NULL, '2026-10-19 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('D7 ORM: DealTable и getList', 'd7-orm-datamanager', 'D7 ORM даёт выборки через Entity и DataManager вместо устаревшего API.', '```php\n<?php\n\nuse Bitrix\\Main\\Loader;\nuse Bitrix\\Crm\\DealTable;\n\nLoader::includeModule(\'crm\');\n\n$rows = DealTable::getList([\n    \'select\' => [\'ID\', \'TITLE\', \'STAGE_ID\', \'OPPORTUNITY\'],\n    \'filter\' => [\n        \'=ASSIGNED_BY_ID\' => 1,\n        \'!STAGE_ID\' => \'WON\',\n    ],\n    \'order\' => [\'ID\' => \'DESC\'],\n    \'limit\' => 20,\n])->fetchAll();\n```\n\nВ фильтрах используют префиксы `=`, `%`, `>`, `!`, `@`.', NULL, '2026-10-20 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Кастомный код в /local и автозагрузка', 'kastomnyj-kod-local', 'Доработки выносят в /local, чтобы обновления продукта не затирали бизнес-логику.', '```php\n<?php\n// /local/php_interface/init.php\n\nuse Bitrix\\Main\\Loader;\n\nLoader::registerAutoLoadClasses(null, [\n    \'Local\\\\Crm\\\\DealHandler\' => \'/local/lib/Crm/DealHandler.php\',\n]);\n```\n\nКогда логики много, оформляйте отдельный модуль `local.*` с install/uninstall.', NULL, '2026-10-21 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Компоненты: result_modifier и epilog', 'komponenty-result-modifier', 'result_modifier и component_epilog позволяют расширять компонент без копирования всей логики ядра.', '```php\n<?php\n// result_modifier.php\n\nif (!defined(\'B_PROLOG_INCLUDED\') || B_PROLOG_INCLUDED !== true) {\n    die();\n}\n\nforeach ($arResult[\'ITEMS\'] as &$item) {\n    $item[\'DISPLAY_TITLE\'] = mb_strtoupper((string) $item[\'NAME\']);\n}\nunset($item);\n```\n\nSQL в шаблон не выносите: данные готовьте в компоненте или modifier.', NULL, '2026-10-22 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Кеш и тегированный сброс', 'kesh-tegirovannyj-sbros', 'Тегированный кеш позволяет точечно сбрасывать связанные данные после изменения сущности CRM.', '```php\n<?php\n\nuse Bitrix\\Main\\Data\\Cache;\nuse Bitrix\\Main\\Application;\n\n$cache = Cache::createInstance();\n$cacheId = \'deal_stats_\' . $userId;\n$cacheDir = \'/local/deal_stats\';\n\nif ($cache->initCache(3600, $cacheId, $cacheDir)) {\n    $result = $cache->getVars();\n} elseif ($cache->startDataCache()) {\n    $result = [\'count\' => 10];\n    $tagged = Application::getInstance()->getTaggedCache();\n    $tagged->startTagCache($cacheDir);\n    $tagged->registerTag(\'crm_deal_list\');\n    $tagged->endTagCache();\n    $cache->endDataCache($result);\n}\n```', NULL, '2026-10-23 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Пользовательские поля UF_ в CRM', 'polzovatelskie-polya-uf-crm', 'UF-поля расширяют карточки CRM без изменения схемы ядра и доступны через add/update/getList.', '```php\n<?php\n\nuse Bitrix\\Main\\Loader;\nuse Bitrix\\Crm\\DealTable;\n\nLoader::includeModule(\'crm\');\n\nDealTable::update(42, [\n    \'UF_CONTRACT_NUMBER\' => \'ДГ-2026/11\',\n]);\n```\n\nДля сложных структур иногда лучше HL-блок или своя таблица модуля.', NULL, '2026-10-24 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Highload-блоки как справочники', 'highload-bloki-spravochniki', 'HL-блоки удобны для справочников, маппингов внешних ID и журналов обмена.', '```php\n<?php\n\nuse Bitrix\\Main\\Loader;\nuse Bitrix\\Highloadblock\\HighloadBlockTable;\n\nLoader::includeModule(\'highloadblock\');\n\n$hl = HighloadBlockTable::getList([\n    \'filter\' => [\'=NAME\' => \'ProductMap\'],\n])->fetch();\n\n$entity = HighloadBlockTable::compileEntity($hl);\n$dataClass = $entity->getDataClass();\n\n$rows = $dataClass::getList([\n    \'select\' => [\'ID\', \'UF_XML_ID\', \'UF_NAME\'],\n    \'filter\' => [\'=UF_ACTIVE\' => 1],\n])->fetchAll();\n```', NULL, '2026-10-25 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Валидация полей сделки на сервере', 'validaciya-polej-sdelki', 'Серверная проверка на OnBeforeCrmDealAdd/Update защищает данные даже при обходе интерфейса.', '```php\n<?php\n\nuse Bitrix\\Main\\EventManager;\n\nEventManager::getInstance()->addEventHandler(\n    \'crm\',\n    \'OnBeforeCrmDealAdd\',\n    static function (array &$fields) {\n        if (empty($fields[\'TITLE\'])) {\n            $GLOBALS[\'APPLICATION\']->ThrowException(\'Укажите название сделки.\');\n            return false;\n        }\n        if (empty($fields[\'COMPANY_ID\']) && empty($fields[\'CONTACT_ID\'])) {\n            $GLOBALS[\'APPLICATION\']->ThrowException(\'Привяжите компанию или контакт.\');\n            return false;\n        }\n        return true;\n    }\n);\n```', NULL, '2026-10-26 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Стадии сделки и реакция на переход', 'stadii-sdelki-perehod', 'STAGE_ID задаёт положение сделки в воронке. На смену стадии вешают проверки, задачи и интеграции.', '```php\n<?php\n\nuse Bitrix\\Main\\Loader;\nuse Bitrix\\Crm\\DealTable;\n\nLoader::includeModule(\'crm\');\n\nDealTable::update(42, [\n    \'STAGE_ID\' => \'PREPAYMENT_INVOICE\',\n]);\n```\n\nВ `OnAfterCrmDealUpdate` сравнивайте старое и новое значение стадии, чтобы сценарий запускался только на реальном переходе.', NULL, '2026-10-27 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Проверка прав на изменение сделки', 'proverka-prav-sdelki', 'Перед обновлением сущности CRM проверяйте права пользователя, иначе автоматизация обойдёт ограничения интерфейса.', '```php\n<?php\n\nuse Bitrix\\Crm\\Service\\Container;\n\n$factory = Container::getInstance()->getFactory(\\CCrmOwnerType::Deal);\n$item = $factory?->getItem(42);\n$userPermissions = Container::getInstance()->getUserPermissions();\n$canUpdate = $userPermissions->item()->canUpdateItem($item);\n```\n\nВ обработчиках явно учитывайте, от чьего лица выполняется операция.', NULL, '2026-10-28 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('CRest и локальные приложения', 'crest-lokalnye-prilozheniya', 'CRest упрощает OAuth локального приложения и вызовы REST без ручной работы с токенами в каждом запросе.', '```php\n<?php\n\nrequire_once __DIR__ . \'/crest.php\';\n\n$result = CRest::call(\'crm.contact.list\', [\n    \'filter\' => [\'=EMAIL\' => \'client@example.com\'],\n    \'select\' => [\'ID\', \'NAME\', \'LAST_NAME\'],\n]);\n\n$contacts = $result[\'result\'] ?? [];\n```\n\nЛокальное приложение удобно, когда нужен placement или управляемые права на методы REST.', NULL, '2026-10-29 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Инфоблоки и CRM: зоны ответственности', 'infobloki-i-crm', 'CRM держит продажи и коммуникации. Инфоблоки — контент и каталог. HL — технические справочники.', 'Короткое правило:\n\n- **CRM** — сделки, лиды, контакты, компании, дела\n- **инфоблоки** — новости, статьи, каталог сайта\n- **HL-блоки** — маппинги и технические таблицы\n\nСвязь между ними делают через API и ключи, а не через «сделку внутри инфоблока».', NULL, '2026-10-30 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Логирование обменов с внешними системами', 'logirovanie-obmenov', 'Интеграции должны писать журнал: сущность, статус, ошибка и время. Иначе сбой обмена не воспроизвести.', '```php\n<?php\n\nuse Bitrix\\Main\\Diag\\Debug;\n\nfunction local_log_exchange(string $message, array $context = []): void\n{\n    $line = date(\'c\') . \' \' . $message . \' \' . json_encode($context, JSON_UNESCAPED_UNICODE);\n    Debug::writeToFile($line, \'\', \'/local/logs/exchange.log\');\n}\n```\n\nКаталог логов закрывайте от HTTP-доступа и настройте ротацию файлов.', NULL, '2026-10-31 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Идемпотентность при повторной доставке события', 'idempotentnost-obmena', 'Повтор вебхука или ретрай агента не должны создавать дубли. Ключ операции сохраняют в журнале.', '```php\n<?php\n\nfunction exportDealOnce(int $dealId, string $externalKey): void\n{\n    if (ExchangeJournal::exists($externalKey)) {\n        return;\n    }\n\n    $response = ExternalApi::send(DealExporter::build($dealId));\n    ExchangeJournal::add([\n        \'UF_KEY\' => $externalKey,\n        \'UF_DEAL_ID\' => $dealId,\n        \'UF_STATUS\' => $response[\'status\'],\n    ]);\n}\n```', NULL, '2026-11-01 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('JS во вкладке CRM: BX.ajax.runAction', 'bx-ajax-runaction', 'Во вкладках placement интерфейс часто собирают на JS и ходят на сервер через BX.ajax.runAction.', '```javascript\nBX.ready(function () {\n  const dealId = Number(BX.message(\'DEAL_ID\'));\n\n  BX.ajax.runAction(\'local:crmtools.api.deal.getSummary\', {\n    data: { dealId }\n  }).then(function (response) {\n    document.getElementById(\'summary\').textContent = response.data.text;\n  });\n});\n```\n\nUI остаётся тонким, бизнес-логика живёт в PHP-action модуля.', NULL, '2026-11-02 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Очередь вместо тяжёлого OnAfter', 'ochered-vmesto-onafter', 'Долгий внешний API не вызывают прямо в OnAfter. Сначала фиксируют задачу, потом обрабатывают пачкой.', '```php\n<?php\n\nQueue::push(\'deal.export\', [\'dealId\' => 42]);\n\nforeach (Queue::pull(\'deal.export\', 50) as $job) {\n    DealExporter::export((int) $job[\'dealId\']);\n}\n```\n\nИнтерфейс CRM не тормозит, а обмен переживает временные сетевые сбои.', NULL, '2026-11-03 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('DateTime в выборках D7', 'datetime-v-d7', 'Для дат в ORM используют Bitrix\\Main\\Type\\DateTime — это надёжнее строк при фильтрах и отчётах.', '```php\n<?php\n\nuse Bitrix\\Main\\Type\\DateTime;\nuse Bitrix\\Crm\\DealTable;\n\n$from = new DateTime(\'2026-11-01 00:00:00\');\n$to = new DateTime(\'2026-11-30 23:59:59\');\n\n$rows = DealTable::getList([\n    \'filter\' => [\n        \'>=DATE_CREATE\' => $from,\n        \'<=DATE_CREATE\' => $to,\n    ],\n    \'select\' => [\'ID\', \'TITLE\', \'DATE_CREATE\'],\n])->fetchAll();\n```', NULL, '2026-11-04 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Товарные позиции сделки', 'produkty-v-sdelke', 'Product rows хранятся отдельно от полей карточки. Их нужно синхронизировать вместе с суммой сделки.', '```php\n<?php\n\nuse Bitrix\\Crm\\ProductRowTable;\n\n$rows = ProductRowTable::getList([\n    \'filter\' => [\n        \'=OWNER_TYPE\' => \'D\',\n        \'=OWNER_ID\' => 42,\n    ],\n    \'select\' => [\'ID\', \'PRODUCT_NAME\', \'PRICE\', \'QUANTITY\'],\n])->fetchAll();\n```', NULL, '2026-11-05 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Исходящие вебхуки портала', 'ishodyashchie-vebhuki', 'Исходящий вебхук уведомляет ваш URL об изменениях CRM. Отвечайте быстро и обрабатывайте полезную нагрузку в фоне.', '```php\n<?php\n\n$payload = json_decode(file_get_contents(\'php://input\'), true) ?: [];\n$event = (string) ($payload[\'event\'] ?? \'\');\n$dealId = (int) ($payload[\'data\'][\'FIELDS\'][\'ID\'] ?? 0);\n\nif ($event === \'ONCRMDEALUPDATE\' && $dealId > 0) {\n    Queue::push(\'deal.export\', [\'dealId\' => $dealId]);\n}\n\nhttp_response_code(200);\necho \'ok\';\n```', NULL, '2026-11-06 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Создание UF-поля из кода модуля', 'sozdanie-uf-iz-koda', 'Поля CRM лучше создавать в install-скрипте модуля, чтобы стенды совпадали без ручных кликов.', '```php\n<?php\n\n$uf = [\n    \'ENTITY_ID\' => \'CRM_DEAL\',\n    \'FIELD_NAME\' => \'UF_CONTRACT_NUMBER\',\n    \'USER_TYPE_ID\' => \'string\',\n    \'MANDATORY\' => \'N\',\n    \'EDIT_FORM_LABEL\' => [\'ru\' => \'Номер договора\'],\n];\n\n$oUserType = new \\CUserTypeEntity();\n$oUserType->Add($uf);\n```', NULL, '2026-11-07 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Безопасный вывод и закрытие /local', 'bezopasnyj-vyvod-local', 'Кастомный PHP в портале нужно писать с экранированием вывода, проверкой прав и закрытием служебных каталогов.', '```php\n<?php\n\necho htmlspecialcharsbx((string) $deal[\'TITLE\']);\n```\n\nЧеклист: ORM вместо склейки SQL, токены не на фронте, логи не доступны по HTTP, before-обработчики проверяют вход.', NULL, '2026-11-08 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Отладка CRM-событий через лог', 'otladka-crm-sobytij', 'Чтобы понять, с какими полями пришёл обработчик, временно пишут входные данные в лог и убирают шум после диагностики.', '```php\n<?php\n\nuse Bitrix\\Main\\Diag\\Debug;\n\nfinal class DealHandler\n{\n    public static function onAfterUpdate(array &$fields): void\n    {\n        Debug::writeToFile($fields, \'OnAfterCrmDealUpdate\', \'/local/logs/crm_events.log\');\n        if (empty($fields[\'ID\'])) {\n            return;\n        }\n    }\n}\n```', NULL, '2026-11-09 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('Сервисный слой над CRM API', 'servisnyj-sloj-crm', 'Прямые вызовы DealTable и CRest лучше прятать за сервисами: проще сопровождать и менять реализацию.', '```php\n<?php\n\nfinal class DealService\n{\n    public function __construct(private DealRepository $deals)\n    {\n    }\n\n    public function markWaitingPayment(int $dealId): void\n    {\n        $this->deals->update($dealId, [\n            \'STAGE_ID\' => \'PREPAYMENT_INVOICE\',\n        ]);\n    }\n}\n```', NULL, '2026-11-10 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('N+1 в выборках CRM', 'n-plus-one-v-crm', 'Список сделок с запросом компании внутри цикла быстро превращается в N+1. Собирайте ID и читайте пакетно.', '```php\n<?php\n\n$companyIds = array_values(array_unique(array_filter(array_column($deals, \'COMPANY_ID\'))));\n$companies = [];\n\nif ($companyIds) {\n    $rs = \\Bitrix\\Crm\\CompanyTable::getList([\n        \'filter\' => [\'@ID\' => $companyIds],\n        \'select\' => [\'ID\', \'TITLE\'],\n    ]);\n    while ($row = $rs->fetch()) {\n        $companies[(int) $row[\'ID\']] = $row;\n    }\n}\n```', NULL, '2026-11-11 10:00:00', 1);

INSERT IGNORE INTO `posts` (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`) VALUES
  ('init.php как точка расширения портала', 'php-interface-init', 'Для небольших доработок достаточно /local/php_interface/init.php: автозагрузка, события и подключение хелперов.', '```php\n<?php\n\nuse Bitrix\\Main\\EventManager;\nuse Bitrix\\Main\\Loader;\n\nLoader::registerAutoLoadClasses(null, [\n    \'Local\\\\Crm\\\\DealHandler\' => \'/local/lib/Crm/DealHandler.php\',\n]);\n\nEventManager::getInstance()->addEventHandler(\n    \'crm\',\n    \'OnAfterCrmDealAdd\',\n    [\'\\\\Local\\\\Crm\\\\DealHandler\', \'onAfterAdd\']\n);\n```\n\nКогда код разрастается, переносите его в модуль `local.*`.', NULL, '2026-11-12 10:00:00', 1);
