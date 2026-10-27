<?php

declare(strict_types=1);

/**
 * Восстановление доступов к MySQL без полной переустановки.
 * Откройте /fix-database.php, сохраните данные, затем УДАЛИТЕ этот файл.
 */

$configPath = __DIR__ . '/config/database.local.php';
$error = null;
$success = null;
$tested = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string) ($_POST['host'] ?? 'localhost'));
    $port = trim((string) ($_POST['port'] ?? '3306'));
    $dbname = trim((string) ($_POST['dbname'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        if ($dbname === '' || $username === '') {
            throw new RuntimeException('Укажите имя базы и пользователя.');
        }

        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname),
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->query('SELECT 1');
        $tested = true;

        $configPhp = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'host'     => " . var_export($host, true) . ",\n"
            . "    'port'     => " . var_export($port, true) . ",\n"
            . "    'dbname'   => " . var_export($dbname, true) . ",\n"
            . "    'username' => " . var_export($username, true) . ",\n"
            . "    'password' => " . var_export($password, true) . ",\n"
            . "    'charset'  => 'utf8mb4',\n"
            . "];\n";

        if (!is_dir(__DIR__ . '/config') && !mkdir(__DIR__ . '/config', 0755, true)) {
            throw new RuntimeException('Не удалось создать папку config.');
        }

        if (file_put_contents($configPath, $configPhp) === false) {
            throw new RuntimeException('Не удалось записать config/database.local.php — проверьте права на папку config.');
        }

        if (!is_dir(__DIR__ . '/storage')) {
            mkdir(__DIR__ . '/storage', 0755, true);
        }
        $lock = __DIR__ . '/storage/installed.lock';
        if (!is_file($lock)) {
            file_put_contents($lock, date('c') . "\n");
        }

        $success = 'Подключение успешно. Файл config/database.local.php сохранён. Удалите fix-database.php с сервера.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

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
  <title>Восстановление доступа к БД</title>
  <style>
    :root { --ink:#14212b; --muted:#5b6b76; --accent:#0f766e; --bg:#f3f6f8; }
    * { box-sizing: border-box; }
    body { margin:0; font-family:system-ui,sans-serif; background:var(--bg); color:var(--ink); min-height:100vh; display:grid; place-items:center; padding:24px; }
    .card { width:min(520px,100%); background:#fff; border:1px solid #d5dee6; padding:32px; box-shadow:0 20px 50px rgba(20,33,43,.08); }
    h1 { font-size:1.35rem; margin:0 0 8px; }
    p { color:var(--muted); line-height:1.5; }
    label { display:block; font-size:.8rem; margin:12px 0 6px; color:var(--muted); }
    input { width:100%; padding:10px 12px; border:1px solid #c9d4dd; font:inherit; }
    button { margin-top:20px; width:100%; border:0; background:var(--accent); color:#fff; padding:12px 16px; font:600 .95rem system-ui,sans-serif; cursor:pointer; }
    .err { background:#fde8e8; color:#9b1c1c; padding:10px 12px; margin-bottom:16px; white-space:pre-line; }
    .ok { background:#e6f6f1; color:#0f5132; padding:10px 12px; margin-bottom:16px; }
    .grid { display:grid; grid-template-columns:1fr 1fr; gap:0 12px; }
    @media (max-width:560px){ .grid{grid-template-columns:1fr;} }
    a { color:var(--accent); }
  </style>
</head>
<body>
  <div class="card">
    <h1>Восстановление доступа к БД</h1>
    <p>Рабочие доступы были затёрты при заливке файлов. Укажите данные MySQL из панели хостинга — они сохранятся в <code>config/database.local.php</code> и больше не будут перезаписываться при обновлении сайта.</p>
    <?php if ($error): ?><div class="err"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="ok"><?= h($success) ?></div>
      <p><a href="./">Открыть сайт</a> · <a href="admin/">Админка</a></p>
    <?php else: ?>
      <form method="post" autocomplete="off">
        <div class="grid">
          <div>
            <label>MySQL host</label>
            <input name="host" value="<?= h($_POST['host'] ?? 'localhost') ?>" required>
          </div>
          <div>
            <label>Port</label>
            <input name="port" value="<?= h($_POST['port'] ?? '3306') ?>" required>
          </div>
        </div>
        <label>Database</label>
        <input name="dbname" value="<?= h($_POST['dbname'] ?? '') ?>" required placeholder="полное имя из панели">
        <div class="grid">
          <div>
            <label>DB user</label>
            <input name="username" value="<?= h($_POST['username'] ?? '') ?>" required>
          </div>
          <div>
            <label>DB password</label>
            <input type="password" name="password" value="">
          </div>
        </div>
        <button type="submit">Проверить и сохранить</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
