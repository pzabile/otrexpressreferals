<?php
declare(strict_types=1);

/**
 * Send a message to the configured Telegram chat. Returns true on
 * success. Errors are silenced — a failed Telegram ping must never
 * block a referral submission.
 *
 * @param array<string, mixed> $config
 */
function telegram_notify(array $config, string $message): bool
{
    $token  = trim((string)($config['telegram_bot_token'] ?? ''));
    $chatId = trim((string)($config['telegram_chat_id']   ?? ''));
    if ($token === '' || $chatId === '') {
        return false;
    }

    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $payload = [
        'chat_id'                  => $chatId,
        'text'                     => $message,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ];

    // Prefer cURL if available; fall back to file_get_contents.
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response !== false && $code >= 200 && $code < 300;
    }

    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'       => http_build_query($payload),
            'timeout'       => 5,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    return $response !== false;
}

/**
 * Build a Telegram message body for a new referral.
 */
function telegram_format_new_referral(array $config, array $referrer, array $referral, ?string $notes): string
{
    $base = rtrim((string)($config['site_url'] ?? ''), '/');
    $adminLink = $base . '/admin/referral.php?id=' . (int)$referral['id'];

    $esc = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

    $lines = [
        '<b>🚛 New Driver Referral</b>',
        '',
        '<b>Driver</b>',
        '• Name: ' . $esc((string)$referral['driver_name']),
        '• Email: ' . $esc((string)$referral['driver_email']),
        '• Phone: ' . $esc((string)$referral['driver_phone']),
        '',
        '<b>Referrer</b>',
        '• Name: ' . $esc((string)$referrer['name']),
        '• Email: ' . $esc((string)$referrer['email']),
        '• Phone: ' . $esc((string)$referrer['phone']),
    ];

    if ($notes !== null && $notes !== '') {
        $lines[] = '';
        $lines[] = '<b>Notes</b>';
        $lines[] = $esc($notes);
    }

    $lines[] = '';
    $lines[] = '<a href="' . $esc($adminLink) . '">Open in admin →</a>';

    return implode("\n", $lines);
}
