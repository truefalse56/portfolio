-- Безопасное наполнение блога: 30 статей для собеседований (ООП, SOLID, PHP).
-- НЕ удаляет и НЕ обновляет существующие записи.
-- Повторный импорт безопасен: INSERT IGNORE + уникальный slug.
-- Как применить на сервере (phpMyAdmin → SQL или mysql CLI):
--   SOURCE /path/to/seed_blog_interview_posts.sql;
-- либо импортировать этот файл в нужную БД.

SET NAMES utf8mb4;

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Что такое ООП и зачем оно нужно', 'chto-takoe-oop', 'ООП — способ организовать код вокруг объектов с состоянием и поведением. На собеседовании ждут не определение из учебника, а зачем это в реальных проектах.', '**ООП (объектно-ориентированное программирование)** — подход, в котором программа строится из объектов: у каждого есть данные (состояние) и методы (поведение).\n\n## Что обычно ждут на собеседовании\n\n- уметь объяснить своими словами, без заученных формулировок\n- назвать четыре принципа: инкапсуляция, наследование, полиморфизм, абстракция\n- привести пример из PHP / веб-проекта\n\n## Зачем это в PHP-проектах\n\nБез ООП можно писать скрипты, но с ростом кода появляется хаос: дублирование, жёсткие связи, сложно тестировать. Классы помогают разделить ответственность: заказ, платёж, пользователь, репозиторий.\n\n```php\n<?php\n\nfinal class Order\n{\n    public function __construct(\n        private readonly int $id,\n        private array $items,\n    ) {\n    }\n\n    public function total(): float\n    {\n        $sum = 0.0;\n        foreach ($this->items as $item) {\n            $sum += $item[\'price\'] * $item[\'qty\'];\n        }\n\n        return $sum;\n    }\n}\n```\n\n## Короткий ответ\n\nООП нужно, чтобы моделировать предметную область, переиспользовать код и менять части системы без переписывания всего файла.', NULL, '2026-10-01 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Инкапсуляция: скрыть детали и открыть контракт', 'inkapsulyaciya', 'Инкапсуляция — это не «private ради private», а контроль доступа к состоянию объекта через понятный публичный интерфейс.', '**Инкапсуляция** — принцип, при котором внутреннее устройство объекта скрыто, а снаружи доступен только нужный контракт (публичные методы).\n\n## Типичный вопрос\n\n«Что такое инкапсуляция?» Плохой ответ: «когда всё private». Хороший: мы защищаем инварианты — объект нельзя привести в некорректное состояние снаружи.\n\n```php\n<?php\n\nfinal class BankAccount\n{\n    public function __construct(private float $balance = 0.0)\n    {\n    }\n\n    public function deposit(float $amount): void\n    {\n        if ($amount <= 0) {\n            throw new InvalidArgumentException(\'Сумма должна быть больше нуля\');\n        }\n        $this->balance += $amount;\n    }\n\n    public function balance(): float\n    {\n        return $this->balance;\n    }\n}\n```\n\n## Зачем это спрашивают\n\nПроверяют, понимаете ли вы границы ответственности класса и почему прямой доступ к полям опасен в больших системах.', NULL, '2026-10-02 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Наследование: когда полезно и когда вредно', 'nasledovanie', 'Наследование даёт переиспользование и иерархию типов, но легко приводит к хрупкому коду. На собеседовании важно знать альтернативу — композицию.', '**Наследование** — механизм, при котором класс получает поведение и/или интерфейс родителя.\n\n## Плюсы\n\n- общий код в базовом классе\n- полиморфизм через общий тип\n- меньше дублирования в простых иерархиях\n\n## Минусы\n\n- жёсткая связь с родителем\n- «god-класс» в корне иерархии\n- сложно менять базовый класс без поломки потомков\n\n```php\n<?php\n\nabstract class Notifier\n{\n    abstract public function send(string $message): void;\n}\n\nfinal class EmailNotifier extends Notifier\n{\n    public function send(string $message): void\n    {\n        // отправка письма\n    }\n}\n```\n\n## Что ответить дополнительно\n\n«Наследование по типу “является” (is-a). Если связь скорее “имеет” (has-a) — лучше композиция».', NULL, '2026-10-03 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Полиморфизм: один интерфейс — разное поведение', 'polimorfizm', 'Полиморфизм позволяет писать код под общий контракт, а конкретную реализацию подставлять позже. Это основа гибких сервисов и DI.', '**Полиморфизм** — способность работать с разными объектами через общий интерфейс или базовый тип.\n\n## Пример, который любят на собеседованиях\n\nЕсть `PaymentGateway`. Код оформления заказа не знает, Stripe это или внутренний биллинг — он вызывает `pay()`.\n\n```php\n<?php\n\ninterface PaymentGateway\n{\n    public function pay(int $amountKopecks): void;\n}\n\nfinal class CheckoutService\n{\n    public function __construct(private PaymentGateway $gateway)\n    {\n    }\n\n    public function checkout(int $amountKopecks): void\n    {\n        $this->gateway->pay($amountKopecks);\n    }\n}\n```\n\n## Виды (кратко)\n\n- **ad-hoc** — перегрузки / разное поведение по типу (в PHP ограничено)\n- **parametric** — generics (в PHP через шаблоны/статический анализ)\n- **subtype** — через наследование и интерфейсы (основной для PHP)\n\nНа практике в PHP почти всегда имеют в виду subtype-полиморфизм.', NULL, '2026-10-04 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Абстракция: выделить важное и спрятать лишнее', 'abstrakciya', 'Абстракция — умение описать сущность на нужном уровне детализации: интерфейс сервиса вместо SQL и HTTP внутри.', '**Абстракция** — фокус на существенных характеристиках объекта и игнорирование деталей реализации.\n\n## Как объяснить на примере\n\nДля клиента `UserRepository` — это «получить пользователя по id». Ему не важно, MySQL это или API.\n\n```php\n<?php\n\ninterface UserRepository\n{\n    public function findById(int $id): ?User;\n}\n```\n\n## Связь с другими принципами\n\nАбстракция часто реализуется через интерфейсы и абстрактные классы. Без неё сложно применять Dependency Inversion и тестировать код на моках.', NULL, '2026-10-05 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Класс, объект, экземпляр: чем отличаются', 'klass-obiekt-ekzemplyar', 'Класс — чертёж, объект — конкретный экземпляр в памяти. Простой вопрос, на котором часто путают термины.', '**Класс** — описание структуры и поведения. **Объект (экземпляр)** — конкретная сущность, созданная по этому описанию.\n\n```php\n<?php\n\nclass User\n{\n    public function __construct(public string $email)\n    {\n    }\n}\n\n$a = new User(\'a@example.com\');\n$b = new User(\'b@example.com\');\n```\n\n`$a` и `$b` — два разных объекта одного класса. У каждого своё состояние.\n\n## Частая ловушка\n\n«Класс = объект» — нет. Класс существует как определение (и как тип), объекты появляются после `new` (или фабрики/контейнера).', NULL, '2026-10-06 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Интерфейс vs абстрактный класс в PHP', 'interfejs-vs-abstraktnyj-klass', 'Интерфейс задаёт контракт без реализации. Абстрактный класс может держать общую логику. Выбор зависит от того, нужна ли общая база поведения.', '## Интерфейс\n\n- только контракт (с PHP 8 можно и default methods в traits, но идея та же: контракт)\n- класс может реализовать несколько интерфейсов\n- удобен для DI и подмены реализаций\n\n## Абстрактный класс\n\n- нельзя создать через `new`\n- может содержать реализованные методы и абстрактные\n- наследование одно (single inheritance)\n\n```php\n<?php\n\ninterface Logger\n{\n    public function info(string $message): void;\n}\n\nabstract class FileStorage\n{\n    public function __construct(protected string $root)\n    {\n    }\n\n    abstract public function put(string $path, string $contents): void;\n}\n```\n\n## Практическое правило\n\nЕсли нужно описать **возможность** («умеет логировать») — интерфейс. Если есть **общее поведение семейства** — абстрактный класс. Часто используют оба.', NULL, '2026-10-07 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('SOLID: Single Responsibility Principle', 'solid-srp', 'SRP: у класса должна быть одна причина для изменения. Не «один метод», а одна зона ответственности.', '**SRP (Single Responsibility)** — класс отвечает за одну задачу / одну причину меняться.\n\n## Плохо\n\nКласс `User` и регистрирует, и шлёт письма, и пишет в БД, и рендерит HTML.\n\n## Лучше\n\n```php\n<?php\n\nfinal class UserRegistrar\n{\n    public function __construct(\n        private UserRepository $users,\n        private Mailer $mailer,\n    ) {\n    }\n\n    public function register(string $email, string $password): void\n    {\n        $user = new User($email, password_hash($password, PASSWORD_DEFAULT));\n        $this->users->save($user);\n        $this->mailer->send($email, \'Добро пожаловать\');\n    }\n}\n```\n\n## Как проверяют на собеседовании\n\nПросят найти нарушение SRP в куске кода или объяснить, почему «толстый» контроллер — проблема.', NULL, '2026-10-08 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('SOLID: Open/Closed Principle', 'solid-ocp', 'OCP: код открыт для расширения и закрыт для модификации. Новое поведение добавляем без правок работающих классов.', '**OCP** — добавляем новое поведение через расширение (новые классы), а не через постоянные правки `if/switch` в старом коде.\n\n## Пример нарушения\n\n```php\n<?php\n\nfunction discount(string $type, float $price): float\n{\n    if ($type === \'vip\') {\n        return $price * 0.9;\n    }\n    if ($type === \'sale\') {\n        return $price * 0.8;\n    }\n\n    return $price;\n}\n```\n\nКаждый новый тип скидки лезет в эту функцию.\n\n## Ближе к OCP\n\n```php\n<?php\n\ninterface DiscountPolicy\n{\n    public function apply(float $price): float;\n}\n\nfinal class VipDiscount implements DiscountPolicy\n{\n    public function apply(float $price): float\n    {\n        return $price * 0.9;\n    }\n}\n```\n\nНовые политики — новые классы, старый код расчёта заказа не трогаем.', NULL, '2026-10-09 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('SOLID: Liskov Substitution Principle', 'solid-lsp', 'LSP: объект наследника должен быть подставляем вместо родителя без поломки ожиданий. Классика — Square/Rectangle.', '**LSP** — если код работает с базовым типом, он должен корректно работать и с любым подтипом.\n\n## Классический антипример\n\nКвадрат наследует прямоугольник и ломает `setWidth`/`setHeight`: ожидания родителя нарушены.\n\n## В PHP на практике\n\nНе сужайте контракт в наследнике: не бросайте неожиданные исключения, не игнорируйте параметры, не меняйте смысл метода.\n\n```php\n<?php\n\ninterface Cache\n{\n    public function get(string $key): ?string;\n    public function set(string $key, string $value, int $ttlSeconds): void;\n}\n```\n\nЕсли `NullCache` реализует интерфейс, `set` может быть no-op, но `get`/`set` не должны внезапно требовать сеть или кидать «not implemented» в местах, где базовый контракт это запрещает. Лучше отдельный интерфейс, если поведение принципиально другое.', NULL, '2026-10-10 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('SOLID: Interface Segregation Principle', 'solid-isp', 'ISP: лучше несколько узких интерфейсов, чем один «божественный». Клиент не должен зависеть от методов, которые не использует.', '**ISP** — не заставляйте класс реализовывать лишние методы «на будущее».\n\n## Плохо\n\n```php\n<?php\n\ninterface Worker\n{\n    public function work(): void;\n    public function eat(): void;\n    public function sleep(): void;\n}\n```\n\nРоботу `eat()` и `sleep()` не нужны — появляются пустые методы или заглушки.\n\n## Лучше\n\nРазделить на `Workable`, `Feedable` и т.д. Класс реализует только то, что умеет.\n\nНа собеседовании часто просят разбить жирный интерфейс репозитория или сервиса на читающий/пишущий контракт.', NULL, '2026-10-11 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('SOLID: Dependency Inversion Principle', 'solid-dip', 'DIP: зависеть от абстракций, а не от деталей. Высокоуровневый модуль не должен знать про конкретный MySQL-драйвер.', '**DIP** — и высокоуровневый, и низкоуровневый код зависят от абстракций (интерфейсов).\n\n## До\n\n`OrderService` создаёт внутри `new MysqliConnection` — тестировать и менять хранилище больно.\n\n## После\n\n```php\n<?php\n\nfinal class OrderService\n{\n    public function __construct(private OrderRepository $orders)\n    {\n    }\n}\n```\n\nКонкретную реализацию (`MysqlOrderRepository`) подставляет контейнер или bootstrap.\n\n## Связь с DI\n\nDependency Injection — способ соблюсти DIP: зависимости передаются снаружи, а не создаются внутри.', NULL, '2026-10-12 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Композиция вместо наследования', 'kompoziciya-vmesto-nasledovaniya', 'Композиция собирает поведение из частей. Гибче наследования и реже ломает архитектуру при изменениях.', '**Композиция** — объект содержит другие объекты и делегирует им работу.\n\n```php\n<?php\n\nfinal class ReportExporter\n{\n    public function __construct(\n        private ReportBuilder $builder,\n        private FileStorage $storage,\n    ) {\n    }\n\n    public function export(int $reportId): string\n    {\n        $content = $this->builder->build($reportId);\n        $path = \'reports/\' . $reportId . \'.csv\';\n        $this->storage->put($path, $content);\n\n        return $path;\n    }\n}\n```\n\n## Когда что выбирать\n\n- **is-a** и стабильная иерархия — наследование\n- нужно собрать поведение из кусков / часто менять части — композиция\n\nФраза «prefer composition over inheritance» — не догма, а предупреждение о цене жёстких иерархий.', NULL, '2026-10-13 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('public, protected, private и final', 'modifikatory-dostupa-i-final', 'Модификаторы доступа задают видимость. final запрещает наследование или переопределение — это тоже инструмент дизайна.', '## Видимость\n\n- **public** — доступно отовсюду\n- **protected** — класс и наследники\n- **private** — только текущий класс\n\n## final\n\n- `final class` — нельзя наследовать\n- `final function` — нельзя переопределить в наследнике\n\n```php\n<?php\n\nfinal class Money\n{\n    public function __construct(private int $kopecks)\n    {\n    }\n\n    public function kopecks(): int\n    {\n        return $this->kopecks;\n    }\n}\n```\n\nValue object часто делают `final`: состояние и инварианты не должны «разъезжаться» в наследниках.\n\nНа собеседовании могут спросить: чем `private` отличается от `protected` и зачем `final` в сервисах.', NULL, '2026-10-14 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Статика в PHP: когда уместна', 'statika-v-php', 'static принадлежит классу, а не экземпляру. Удобно для чистых утилит, опасно для скрытого глобального состояния.', '**Статические свойства/методы** живут на уровне класса.\n\n## Уместно\n\n- чистые хелперы без состояния: `Str::slug()`\n- фабрики без зависимостей\n- константные реестры (осторожно)\n\n## Опасно\n\n- статический DB-коннект «на весь мир»\n- `App::getContainer()` как service locator повсюду\n- сложно тестировать и подменять\n\n```php\n<?php\n\nfinal class PriceFormatter\n{\n    public static function rub(int $kopecks): string\n    {\n        return number_format($kopecks / 100, 2, \'.\', \' \') . \' ₽\';\n    }\n}\n```\n\nЕсли методу нужны зависимости — делайте обычный сервис и инъектируйте его.', NULL, '2026-10-15 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Магические методы PHP: __construct, __get, __toString и другие', 'magicheskie-metody-php', 'Магические методы вмешиваются в жизненный цикл объекта. Их полезно знать, но злоупотребление ухудшает читаемость.', 'Самые частые на собеседованиях:\n\n- `__construct` / `__destruct`\n- `__get` / `__set` / `__isset` / `__unset`\n- `__call` / `__callStatic`\n- `__toString`\n- `__invoke`\n- `__clone` / `__serialize` / `__unserialize`\n\n```php\n<?php\n\nfinal class Route\n{\n    public function __construct(private string $path)\n    {\n    }\n\n    public function __toString(): string\n    {\n        return $this->path;\n    }\n}\n```\n\n## Совет\n\nНе прячьте важную бизнес-логику только в `__get`/`__call` — это усложняет отладку. Для DTO и value object `__toString`/`__invoke` часто уместны.', NULL, '2026-10-16 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Traits в PHP: переиспользование без наследования', 'traits-v-php', 'Trait подмешивает методы в класс. Это не замена наследования и не повод размазать состояние по проекту.', '**Trait** — механизм горизонтального переиспользования кода при одиночном наследовании PHP.\n\n```php\n<?php\n\ntrait Timestampable\n{\n    private ?DateTimeImmutable $createdAt = null;\n\n    public function touch(): void\n    {\n        $this->createdAt = new DateTimeImmutable();\n    }\n}\n\nfinal class Article\n{\n    use Timestampable;\n}\n```\n\n## Вопросы на собеседовании\n\n- чем trait отличается от наследования\n- что будет при конфликте имён методов двух traits\n- почему trait — не архитектурная серебряная пуля\n\nЕсли trait тащит скрытые зависимости и кучу состояния — лучше явная композиция.', NULL, '2026-10-17 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Namespaces и PSR-4 autoload', 'namespaces-i-psr-4', 'Пространства имён убирают конфликты имён. PSR-4 связывает namespace с путём файла — основа Composer-автозагрузки.', '```php\n<?php\n\nnamespace App\\Service;\n\nuse App\\Repository\\UserRepository;\n\nfinal class AuthService\n{\n    public function __construct(private UserRepository $users)\n    {\n    }\n}\n```\n\n## PSR-4 коротко\n\n`App\\` → каталог `src/` (или `app/`). Класс `App\\Service\\AuthService` лежит в `app/Service/AuthService.php`.\n\nComposer читает секцию `autoload.psr-4` в `composer.json` и генерирует автозагрузчик. После смены mapping нужен `composer dump-autoload`.\n\nНа собеседовании часто просят объяснить `use`, alias (`as`) и зачем namespaces в больших проектах.', NULL, '2026-10-18 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Типизация в PHP: type hints, union, nullable', 'tipizaciya-v-php', 'Строгие типы ловят ошибки раньше и делают контракты явными. На собеседованиях ждут понимание ?Type, union и void/never.', '```php\n<?php\n\ndeclare(strict_types=1);\n\nfunction findUser(int $id): ?User\n{\n    // User|null\n}\n\nfunction respond(int|string $code): void\n{\n}\n```\n\n## Что полезно помнить\n\n- `declare(strict_types=1);` влияет на **вызовы функций в этом файле**\n- `mixed` — всё подряд, использовать осознанно\n- `never` — функция не возвращает управление (exit/throw)\n- readonly свойства и constructor property promotion упрощают DTO\n\nТипы — часть документации и защиты API класса.', NULL, '2026-10-19 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Исключения в PHP: Exception, try/catch, когда кидать', 'isklyucheniya-v-php', 'Исключения сигнализируют о сбое контракта. Не используйте их для обычного ветвления бизнес-логики.', '```php\n<?php\n\nfinal class UserNotFoundException extends RuntimeException\n{\n}\n\nfunction getUser(UserRepository $repo, int $id): User\n{\n    $user = $repo->findById($id);\n    if ($user === null) {\n        throw new UserNotFoundException(\'User #\' . $id . \' not found\');\n    }\n\n    return $user;\n}\n```\n\n## Практика\n\n- ловите только то, что можете обработать\n- не глотайте `catch (Throwable) {}` без лога\n- для ожидаемых сценариев («email занят») иногда лучше Result/валидация, чем Exception\n\nНа собеседовании могут спросить разницу `Exception` и `Error`, а также зачем свои exception-классы.', NULL, '2026-10-20 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('PDO и SQL-инъекции: подготовленные запросы', 'pdo-i-sql-inekcii', 'Подготовленные выражения отделяют запрос от данных. Склеивать SQL строкой с вводом пользователя нельзя.', '```php\n<?php\n\n$stmt = $pdo->prepare(\'SELECT * FROM users WHERE email = ?\');\n$stmt->execute([$email]);\n$user = $stmt->fetch();\n```\n\n## Почему это важно\n\nSQL-инъекция — классика собеседований по безопасности. Даже один «безопасный» concat в отчёте может открыть дыру.\n\n## Дополнительно\n\n- используйте `PDO::ATTR_ERRMODE => EXCEPTION`\n- не эмулируйте prepare без нужды (`ATTR_EMULATE_PREPARES => false` там, где драйвер позволяет)\n- для динамических имён колонок/таблиц prepared statements не спасают — нужен whitelist', NULL, '2026-10-21 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('HTTP-методы и основы REST API', 'http-metody-i-rest', 'GET читает, POST создаёт, PUT/PATCH обновляют, DELETE удаляет. REST — стиль API вокруг ресурсов и статусов.', '## Методы\n\n- **GET** — чтение, без побочных эффектов\n- **POST** — создание / неидемпотентные действия\n- **PUT** — полная замена ресурса\n- **PATCH** — частичное обновление\n- **DELETE** — удаление\n\n## Что спросят\n\n- идемпотентность (`PUT`/`DELETE` vs `POST`)\n- коды: 200, 201, 204, 400, 401, 403, 404, 422, 500\n- зачем `Content-Type: application/json`\n\n```php\n<?php\n\n// Упрощённо в контроллере\nhttp_response_code(201);\nheader(\'Content-Type: application/json; charset=UTF-8\');\necho json_encode([\'id\' => $id], JSON_UNESCAPED_UNICODE);\n```', NULL, '2026-10-22 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Сессии и cookies: как PHP помнит пользователя', 'sessii-i-cookies', 'Cookie хранит идентификатор на клиенте, сессия — данные на сервере. Важно понимать безопасность session fixation и XSS.', 'После `session_start()` PHP обычно ставит cookie с session id. Данные лежат на сервере (файлы/Redis), в браузере — только ключ.\n\n## Частые темы собеседования\n\n- разница cookie и session\n- `HttpOnly`, `Secure`, `SameSite`\n- session fixation: после логина делать `session_regenerate_id(true)`\n- почему нельзя хранить пароль в сессии в открытом виде\n\n```php\n<?php\n\nsession_start();\nsession_regenerate_id(true);\n$_SESSION[\'user_id\'] = $user->id;\n```', NULL, '2026-10-23 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('MVC: как разделить ответственность в веб-приложении', 'mvc-v-veb-prilozhenii', 'Model — данные и правила, View — отображение, Controller — вход HTTP и координация. Толстые контроллеры — частая ошибка.', '## Роли\n\n- **Controller** — принять запрос, валидировать вход, вызвать сервис/модель, вернуть ответ\n- **Model / Domain / Service** — бизнес-правила\n- **View / Template** — HTML/JSON представление\n\n## Плохо\n\nКонтроллер на 300 строк с SQL и бизнес-логикой.\n\n## Лучше\n\nКонтроллер тонкий, логика в сервисах, SQL в репозиториях.\n\nНа собеседовании могут спросить, где держать валидацию и почему шаблон не должен ходить в БД.', NULL, '2026-10-24 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Dependency Injection и контейнер', 'dependency-injection', 'DI — зависимости передаются снаружи. Контейнер собирает граф объектов, но service locator в бизнес-коде лучше не размазывать.', '```php\n<?php\n\nfinal class InvoiceService\n{\n    public function __construct(\n        private InvoiceRepository $invoices,\n        private Clock $clock,\n    ) {\n    }\n}\n```\n\n## Зачем\n\n- проще тестировать (подмена зависимостей)\n- явные контракты класса\n- меньше `new` внутри бизнес-логики\n\n## Осторожно\n\n`App::get(Mailer::class)` повсюду — это service locator. Удобно, но скрывает зависимости. Предпочтительнее constructor injection.', NULL, '2026-10-25 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Паттерн Singleton: суть и критика', 'pattern-singleton', 'Singleton гарантирует один экземпляр. На собеседованиях его знают все — и почти всегда обсуждают, почему он вреден.', '```php\n<?php\n\nfinal class LegacyConfig\n{\n    private static ?self $instance = null;\n\n    private function __construct()\n    {\n    }\n\n    public static function getInstance(): self\n    {\n        return self::$instance ??= new self();\n    }\n}\n```\n\n## Минусы\n\n- скрытая глобальная зависимость\n- сложно тестировать\n- проблемы с состоянием в долгоживущих процессах\n\nСовременный ответ: «Знаю, как реализовать, но в приложении предпочту DI и явный lifecycle объекта».', NULL, '2026-10-26 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Паттерн Factory: создание объектов без new повсюду', 'pattern-factory', 'Фабрика инкапсулирует создание объектов. Полезно, когда создание сложное или зависит от конфигурации.', '```php\n<?php\n\nfinal class NotifierFactory\n{\n    public function __construct(private array $config)\n    {\n    }\n\n    public function create(string $channel): Notifier\n    {\n        return match ($channel) {\n            \'email\' => new EmailNotifier($this->config[\'smtp\']),\n            \'sms\' => new SmsNotifier($this->config[\'sms\']),\n            default => throw new InvalidArgumentException(\'Unknown channel\'),\n        };\n    }\n}\n```\n\n## Что отличить\n\n- **Simple factory** — обычный класс/метод создания\n- **Factory Method** — создание делегируется подклассам\n- **Abstract Factory** — семейства связанных продуктов\n\nНа junior/middle достаточно объяснить идею и показать пример.', NULL, '2026-10-27 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Паттерн Strategy: сменяемые алгоритмы', 'pattern-strategy', 'Strategy выносит семейство алгоритмов в отдельные классы с общим интерфейсом. Часто пересекается с OCP.', '```php\n<?php\n\ninterface SortStrategy\n{\n    /** @param list<int> $items */\n    public function sort(array $items): array;\n}\n\nfinal class Sorter\n{\n    public function __construct(private SortStrategy $strategy)\n    {\n    }\n\n    public function run(array $items): array\n    {\n        return $this->strategy->sort($items);\n    }\n}\n```\n\nПримеры из жизни: расчёт доставки, скидки, выбор парсера прайса, разные способы авторизации.', NULL, '2026-10-28 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('Паттерн Repository: доступ к данным', 'pattern-repository', 'Repository прячет детали хранения и отдаёт приложению коллекции/сущности. Удобно для смены БД и тестов.', '```php\n<?php\n\ninterface OrderRepository\n{\n    public function find(int $id): ?Order;\n    public function save(Order $order): void;\n}\n```\n\nДоменный код работает с `OrderRepository`, а не с SQL. В реализации может быть PDO, ORM или внешний API.\n\n## Частый вопрос\n\n«Чем repository отличается от DAO?» На практике границы размыты; важно, что бизнес-слой не знает про детали хранения.', NULL, '2026-10-29 10:00:00', 1);

INSERT IGNORE INTO `posts`
  (`title`, `slug`, `excerpt`, `content`, `cover_image`, `published_at`, `is_published`)
VALUES
  ('N+1 запросов и как его заметить', 'n-plus-one-zaprosy', 'N+1 — один запрос списка и ещё N запросов в цикле. Типичная причина тормозов в ORM и самописных репозиториях.', '## Как выглядит\n\n```php\n<?php\n\n$users = $pdo->query(\'SELECT * FROM users\')->fetchAll();\nforeach ($users as $user) {\n    $stmt = $pdo->prepare(\'SELECT * FROM orders WHERE user_id = ?\');\n    $stmt->execute([$user[\'id\']]);\n    $orders = $stmt->fetchAll();\n}\n```\n\n1 + N запросов вместо одного JOIN/`WHERE user_id IN (...)`.\n\n## Как чинить\n\n- жадная загрузка / batch-запрос\n- JOIN или два запроса + группировка в PHP\n- кэш там, где уместно\n\nНа собеседовании любят дать псевдокод и попросить найти проблему производительности.', NULL, '2026-10-30 10:00:00', 1);

-- Ожидается до 30 новых строк (меньше, если часть slug уже есть).
