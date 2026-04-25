<?php
/**
 * Shared bootstrap — included by every public-facing page.
 *
 * Loads config.php, opens the DB connection, starts the session with
 * secure cookie defaults, and exposes $config / $db globals.
 */

declare(strict_types=1);

if (!defined('OTR_ROOT')) {
    define('OTR_ROOT', dirname(__DIR__));
}

$configPath = OTR_ROOT . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo '<h1>Configuration missing</h1>';
    echo '<p>Copy <code>config.example.php</code> to <code>config.php</code> and fill in your database credentials, then visit <a href="/install.php">install.php</a>.</p>';
    exit;
}

/** @var array<string, mixed> $config */
$config = require $configPath;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/statuses.php';
require_once __DIR__ . '/auth.php';

$db = otr_db($config);

if (session_status() === PHP_SESSION_NONE) {
    session_name((string)($config['session_name'] ?? 'otr_ref_sess'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
