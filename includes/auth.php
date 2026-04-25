<?php
declare(strict_types=1);

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function admin_current(PDO $db): ?array
{
    if (!admin_is_logged_in()) {
        return null;
    }
    $stmt = $db->prepare('SELECT id, email FROM admin_users WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function admin_login(PDO $db, string $email, string $password): ?array
{
    $email = strtolower(trim($email));
    if ($email === '' || $password === '') {
        return null;
    }
    $stmt = $db->prepare('SELECT id, email, password_hash FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return null;
    }
    // Rotate session ID on successful login to prevent fixation.
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$row['id'];
    return ['id' => (int)$row['id'], 'email' => $row['email']];
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

function require_admin(PDO $db): array
{
    $admin = admin_current($db);
    if ($admin === null) {
        redirect('/admin/login.php');
    }
    return $admin;
}
