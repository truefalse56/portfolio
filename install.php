<?php

declare(strict_types=1);

/**
 * Одноразовая установка БД.
 * Откройте /install.php в браузере, затем удалите этот файл.
 */

$lockFile = __DIR__ . '/storage/installed.lock';
$error = null;
$success = null;
$step = is_file($lockFile) ? 'locked' : 'form';

$configPath = __DIR__ . '/config/database.local.php';

if ($step === 'form' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string) ($_POST['host'] ?? '127.0.0.1'));
    $port = trim((string) ($_POST['port'] ?? '3306'));
    $dbname = trim((string) ($_POST['dbname'] ?? 'dev_portfolio'));
    $username = trim((string) ($_POST['username'] ?? 'root'));
    $password = (string) ($_POST['password'] ?? '');
    $adminUser = trim((string) ($_POST['admin_user'] ?? 'admin'));
    $adminPass = (string) ($_POST['admin_pass'] ?? '');
    $adminName = trim((string) ($_POST['admin_name'] ?? 'Килин В.А.'));

    try {
        if ($adminUser === '' || strlen($adminPass) < 6) {
            throw new RuntimeException('Логин обязателен, пароль — не короче 6 символов.');
        }
        if ($dbname === '' || $username === '') {
            throw new RuntimeException('Укажите имя базы и пользователя MySQL.');
        }

        $pdoOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ];

        // На shared-хостинге БД уже создана в панели — подключаемся сразу к ней
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname),
                $username,
                $password,
                $pdoOptions
            );
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '1045') || str_contains($msg, 'Access denied')) {
                throw new RuntimeException(
                    "Доступ к MySQL отклонён (ошибка 1045).\n\n"
                    . "Проверьте в панели хостинга:\n"
                    . "1) точный логин пользователя БД (часто с префиксом, напр. svetl136_portfol);\n"
                    . "2) пароль — лучше сбросить и вставить заново, без лишних пробелов;\n"
                    . "3) пользователь привязан именно к этой базе;\n"
                    . "4) Host обычно localhost (иногда 127.0.0.1).\n\n"
                    . 'Техническая деталь: ' . $msg
                );
            }
            if (str_contains($msg, '1049') || str_contains($msg, 'Unknown database')) {
                throw new RuntimeException(
                    "База «{$dbname}» не найдена. Создайте её в панели хостинга и укажите полное имя "
                    . '(часто с префиксом аккаунта), затем повторите установку.'
                );
            }
            throw $e;
        }

        $sqlFile = __DIR__ . '/sql/schema.sql';
        if (!is_file($sqlFile)) {
            throw new RuntimeException('Не найден sql/schema.sql');
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new RuntimeException('Не удалось прочитать schema.sql');
        }

        // Убираем строковые комментарии и режем по `;` вне кавычек
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $statements = [];
        $buffer = '';
        $inString = false;
        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            $ch = $sql[$i];
            if ($ch === "'") {
                // SQL-экранирование кавычки: ''
                if ($inString && $i + 1 < $length && $sql[$i + 1] === "'") {
                    $buffer .= "''";
                    $i++;
                    continue;
                }
                $inString = !$inString;
                $buffer .= $ch;
                continue;
            }
            if ($ch === ';' && !$inString) {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }
            $buffer .= $ch;
        }
        $tail = trim($buffer);
        if ($tail !== '') {
            $statements[] = $tail;
        }

        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }

        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO admins (username, password_hash, name) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), name = VALUES(name)'
        );
        $stmt->execute([$adminUser, $hash, $adminName]);

        $configPhp = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'host'     => " . var_export($host, true) . ",\n"
            . "    'port'     => " . var_export($port, true) . ",\n"
            . "    'dbname'   => " . var_export($dbname, true) . ",\n"
            . "    'username' => " . var_export($username, true) . ",\n"
            . "    'password' => " . var_export($password, true) . ",\n"
            . "    'charset'  => 'utf8mb4',\n"
            . "];\n";

        if (file_put_contents($configPath, $configPhp) === false) {
            throw new RuntimeException('Не удалось записать config/database.local.php');
        }

        if (!is_dir(__DIR__ . '/storage')) {
            mkdir(__DIR__ . '/storage', 0755, true);
        }
        file_put_contents($lockFile, date('c') . "\n");

        if (!is_dir(__DIR__ . '/assets/uploads')) {
            mkdir(__DIR__ . '/assets/uploads', 0755, true);
        }

        $success = 'Установка завершена. Удалите файл install.php с сервера.';
        $step = 'done';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$hostDefault = $_POST['host'] ?? 'localhost';
$portDefault = $_POST['port'] ?? '3306';
$dbnameDefault = $_POST['dbname'] ?? '';
$userDefault = $_POST['username'] ?? '';


function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Установка портфолио</title>
  <style>
    :root { --ink:#14212b; --muted:#5b6b76; --accent:#0f766e; --bg:#f3f6f8; --card:#fff; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: system-ui, sans-serif; background:
      radial-gradient(1200px 600px at 10% -10%, #d7ece8 0%, transparent 55%),
      radial-gradient(900px 500px at 100% 0%, #e8eef5 0%, transparent 50%),
      var(--bg); color: var(--ink); min-height:100vh; display:grid; place-items:center; padding:24px; }
    .card { width:min(520px,100%); background:var(--card); border:1px solid #d5dee6; padding:32px; box-shadow: 0 20px 50px rgba(20,33,43,.08); }
    h1 { font-size:1.4rem; margin:0 0 8px; letter-spacing:-.02em; }
    p { color:var(--muted); margin:0 0 20px; line-height:1.5; font-size:.95rem; }
    label { display:block; font-size:.8rem; margin:12px 0 6px; color:var(--muted); }
    input { width:100%; padding:10px 12px; border:1px solid #c9d4dd; font:inherit; background:#fbfcfd; }
    input:focus { outline:2px solid #99d5cf; border-color:var(--accent); }
    button { margin-top:20px; width:100%; border:0; background:var(--accent); color:#fff; padding:12px 16px; font:600 .95rem system-ui,sans-serif; cursor:pointer; }
    button:hover { background:#0b5f59; }
    .err { background:#fde8e8; color:#9b1c1c; padding:10px 12px; margin-bottom:16px; font-size:.9rem; white-space:pre-line; }
    .ok { background:#e6f6f1; color:#0f5132; padding:10px 12px; margin-bottom:16px; font-size:.9rem; }
    .hint { font-size:.8rem; color:var(--muted); margin:4px 0 0; }
    .grid { display:grid; grid-template-columns:1fr 1fr; gap:0 12px; }
    @media (max-width:560px){ .grid{grid-template-columns:1fr;} }
    a { color:var(--accent); }
    code { font-size:.85em; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Установка портфолио</h1>
    <?php if ($step === 'locked'): ?>
      <p>Сайт уже установлен. Удалите <code>install.php</code> и файл <code>storage/installed.lock</code>, если нужна переустановка.</p>
      <p><a href="./">На главную</a> · <a href="admin/">Админка</a></p>
    <?php elseif ($step === 'done'): ?>
      <div class="ok"><?= h($success) ?></div>
      <p><a href="admin/">Перейти в админ-панель</a></p>
    <?php else: ?>
      <p>Базу и пользователя создайте заранее в панели хостинга, затем укажите их ниже.</p>
      <?php if ($error): ?><div class="err"><?= h($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="grid">
          <div>
            <label>MySQL host</label>
            <input name="host" value="<?= h($hostDefault) ?>" required>
            <p class="hint">Обычно localhost</p>
          </div>
          <div>
            <label>Port</label>
            <input name="port" value="<?= h($portDefault) ?>" required>
          </div>
        </div>
        <label>Database (полное имя)</label>
        <input name="dbname" value="<?= h($dbnameDefault) ?>" required placeholder="например svetl136_portfolio">
        <p class="hint">Имя из панели, часто с префиксом аккаунта</p>
        <div class="grid">
          <div>
            <label>DB user</label>
            <input name="username" value="<?= h($userDefault) ?>" required placeholder="например svetl136_portfol">
          </div>
          <div>
            <label>DB password</label>
            <input type="password" name="password" value="">
          </div>
        </div>
        <p class="hint">Пользователь должен быть привязан к этой базе в панели хостинга</p>
        <label>Логин администратора</label>
        <input name="admin_user" value="<?= h($_POST['admin_user'] ?? 'admin') ?>" required>
        <label>Пароль администратора</label>
        <input type="password" name="admin_pass" required minlength="6" placeholder="минимум 6 символов">
        <label>Имя в админке</label>
        <input name="admin_name" value="<?= h($_POST['admin_name'] ?? 'Килин В.А.') ?>">
        <button type="submit">Установить</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
