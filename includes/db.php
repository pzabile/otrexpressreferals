<?php
declare(strict_types=1);

/**
 * Return a shared PDO connection built from $config.
 *
 * @param array<string, mixed> $config
 */
function otr_db(array $config): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string)$config['db_host'];
    $port = (int)($config['db_port'] ?? 3306);
    $name = (string)$config['db_name'];
    $user = (string)$config['db_user'];
    $pass = (string)$config['db_pass'];
    $charset = (string)($config['db_charset'] ?? 'utf8mb4');

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $host,
        $port,
        $name,
        $charset
    );

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>Check your credentials in <code>config.php</code>. Error: '
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        exit;
    }

    return $pdo;
}
