<?php
/**
 * OTR Express Driver Referrals — configuration.
 *
 * 1. Copy this file to config.php
 * 2. Fill in your Hostinger MySQL credentials (from hPanel → Databases)
 * 3. Open https://referrals.otrexpressgroup.com/install.php in your
 *    browser once to create tables and your admin user, then delete
 *    install.php.
 *
 * config.php is .gitignored and will never be pushed to GitHub.
 */

return [
    // --- MySQL connection (from hPanel → Databases → Management) ---
    'db_host'  => 'localhost',
    'db_port'  => 3306,
    'db_name'  => 'u000000000_referrals',
    'db_user'  => 'u000000000_referrals',
    'db_pass'  => 'your-database-password',
    'db_charset' => 'utf8mb4',

    // --- Site ---
    'site_name'       => 'OTR Express Group Driver Referrals',
    'site_url'        => 'https://referrals.otrexpressgroup.com',
    'referral_bounty' => 200, // USD shown on the public site

    // --- Session ---
    // Name of the PHP session cookie for admin login.
    'session_name'    => 'otr_ref_sess',
    // HMAC secret for CSRF tokens. Any 32+ character random string.
    'app_secret'      => 'REPLACE-ME-WITH-A-LONG-RANDOM-STRING-AT-LEAST-32-CHARS',

    // --- Telegram notifications (optional) ---
    // Get a token from @BotFather, find your chat id from @userinfobot.
    // Leave both blank to disable Telegram pings.
    'telegram_bot_token' => '',
    'telegram_chat_id'   => '',

    // --- Company info shown in Terms ---
    'legal_entity'    => 'Benux Corp',
    'legal_dba'       => 'OTR Express Group',
    'legal_email'     => 'info@otrexpressgroup.com',
    'legal_state'     => 'Florida', // change to your state of formation
];
