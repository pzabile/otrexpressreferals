<?php
declare(strict_types=1);

/**
 * HTML-escape a value for output.
 */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a datetime string for display. Returns "—" if null/empty.
 */
function fmt_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    $ts = strtotime($value);
    if ($ts === false) {
        return e($value);
    }
    return date('M j, Y g:i a', $ts);
}

function fmt_short_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    $ts = strtotime($value);
    if ($ts === false) {
        return e($value);
    }
    return date('M j, Y', $ts);
}

/**
 * Digits-only version of a phone, for loose matching.
 */
function phone_digits(?string $value): string
{
    if ($value === null) {
        return '';
    }
    return preg_replace('/\D+/', '', $value) ?? '';
}

/**
 * Normalize a phone to a pretty display format when possible.
 */
function phone_pretty(string $value): string
{
    $digits = phone_digits($value);
    if (strlen($digits) === 10) {
        return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
    }
    if (strlen($digits) === 11 && $digits[0] === '1') {
        return sprintf('+1 (%s) %s-%s', substr($digits, 1, 3), substr($digits, 4, 3), substr($digits, 7));
    }
    return trim($value);
}

/**
 * Validate an email. Returns the lowercased trimmed email or null.
 */
function clean_email(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $value = strtolower(trim($value));
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
}

/**
 * Issue a CSRF token tied to the current session.
 */
function csrf_token(array $config): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    $secret = (string)($config['app_secret'] ?? '');
    return hash_hmac('sha256', (string)$_SESSION['_csrf'], $secret);
}

/**
 * Verify a submitted CSRF token.
 */
function csrf_check(array $config, ?string $submitted): bool
{
    if (!is_string($submitted) || $submitted === '') {
        return false;
    }
    $expected = csrf_token($config);
    return hash_equals($expected, $submitted);
}

function csrf_field(array $config): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token($config)) . '">';
}

/**
 * Store a one-time flash message in the session.
 */
function flash_set(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    $msg = $_SESSION['_flash'][$key] ?? null;
    if ($msg !== null) {
        unset($_SESSION['_flash'][$key]);
    }
    return $msg;
}

/**
 * Redirect and stop execution.
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Build an absolute URL relative to the site root.
 */
function url(array $config, string $path = '/'): string
{
    $base = rtrim((string)($config['site_url'] ?? ''), '/');
    if ($path === '' || $path[0] !== '/') {
        $path = '/' . $path;
    }
    return $base . $path;
}
