<?php
declare(strict_types=1);

function cms_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $database = cms_config('database', []);
    $host = (string) ($database['host'] ?? '127.0.0.1');
    $port = (int) ($database['port'] ?? 3306);
    $name = (string) ($database['name'] ?? '');
    $username = (string) ($database['username'] ?? '');
    $password = (string) ($database['password'] ?? '');
    $charset = (string) ($database['charset'] ?? 'utf8mb4');

    if ($name === '' || $username === '') {
        throw new RuntimeException('Update config/cms.php with your MySQL database credentials before using the CMS.');
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        throw new RuntimeException('Unable to connect to the CMS database. Check config/cms.php and confirm MySQL is running.', 0, $exception);
    }

    return $pdo;
}

function cms_try_db(): ?PDO
{
    try {
        return cms_db();
    } catch (Throwable) {
        return null;
    }
}

function cms_table_exists(string $table): bool
{
    $pdo = cms_try_db();

    if (!$pdo instanceof PDO) {
        return false;
    }

    $statement = $pdo->prepare('SHOW TABLES LIKE :table_name');
    $statement->execute(['table_name' => $table]);

    return (bool) $statement->fetchColumn();
}

function cms_run_schema(): void
{
    $schemaPath = cms_root('database/cms.sql');

    if (!is_file($schemaPath)) {
        throw new RuntimeException('Missing database/cms.sql.');
    }

    $sql = file_get_contents($schemaPath);

    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('The CMS schema file is empty.');
    }

    cms_db()->exec($sql);
}
