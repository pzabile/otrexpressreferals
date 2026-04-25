<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/telegram.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/refer.php');
}

if (!csrf_check($config, (string)($_POST['_csrf'] ?? ''))) {
    http_response_code(400);
    echo 'Invalid form token. Please reload and try again.';
    exit;
}

$referrerName  = trim((string)($_POST['referrer_name'] ?? ''));
$referrerEmail = clean_email($_POST['referrer_email'] ?? '');
$referrerPhone = trim((string)($_POST['referrer_phone'] ?? ''));
$driverName    = trim((string)($_POST['driver_name'] ?? ''));
$driverEmail   = clean_email($_POST['driver_email'] ?? '');
$driverPhone   = trim((string)($_POST['driver_phone'] ?? ''));
$notes         = trim((string)($_POST['notes'] ?? ''));
$consentDriver = !empty($_POST['consent_driver']);
$consentTerms  = !empty($_POST['consent_terms']);
$consentSelf   = !empty($_POST['consent_self']);

$errors = [];
if ($referrerName === '')  $errors[] = 'Your name is required.';
if ($referrerEmail === null) $errors[] = 'Your email is required and must be valid.';
if (strlen(phone_digits($referrerPhone)) < 7) $errors[] = 'Your phone is required.';
if ($driverName === '')    $errors[] = 'Driver name is required.';
if ($driverEmail === null) $errors[] = 'Driver email is required and must be valid.';
if (strlen(phone_digits($driverPhone)) < 7) $errors[] = 'Driver phone is required.';
if (strlen($notes) > 1000) $errors[] = 'Notes are limited to 1000 characters.';
if (!$consentDriver) $errors[] = 'You must confirm the driver has agreed to be contacted.';
if (!$consentTerms)  $errors[] = 'You must agree to the Terms & Conditions.';
if (!$consentSelf)   $errors[] = 'You must consent to be contacted about this referral.';

if (!empty($errors)) {
    $_SESSION['_old_refer'] = [
        'referrer_name'  => $referrerName,
        'referrer_email' => (string)$referrerEmail,
        'referrer_phone' => $referrerPhone,
        'driver_name'    => $driverName,
        'driver_email'   => (string)$driverEmail,
        'driver_phone'   => $driverPhone,
        'notes'          => $notes,
        'consent_driver' => $consentDriver,
        'consent_terms'  => $consentTerms,
        'consent_self'   => $consentSelf,
    ];
    $_SESSION['_errors_refer'] = $errors;
    redirect('/refer.php');
}

try {
    $db->beginTransaction();

    // Upsert referrer by email.
    $stmt = $db->prepare('SELECT id FROM referrers WHERE email = ? LIMIT 1');
    $stmt->execute([$referrerEmail]);
    $row = $stmt->fetch();
    if ($row) {
        $referrerId = (int)$row['id'];
        $upd = $db->prepare('UPDATE referrers SET name = ?, phone = ? WHERE id = ?');
        $upd->execute([$referrerName, $referrerPhone, $referrerId]);
    } else {
        $ins = $db->prepare('INSERT INTO referrers (name, email, phone) VALUES (?, ?, ?)');
        $ins->execute([$referrerName, $referrerEmail, $referrerPhone]);
        $referrerId = (int)$db->lastInsertId();
    }

    $ins = $db->prepare('INSERT INTO referrals
        (referrer_id, driver_name, driver_email, driver_phone, status)
        VALUES (?, ?, ?, ?, "SUBMITTED")');
    $ins->execute([$referrerId, $driverName, $driverEmail, $driverPhone]);
    $referralId = (int)$db->lastInsertId();

    $stage = $db->prepare('INSERT INTO stage_updates (referral_id, status, note) VALUES (?, ?, ?)');
    $stage->execute([$referralId, 'SUBMITTED', 'Referral received from referrer.']);

    // Persist a system comment with the consent record so it's auditable.
    $consentBody = sprintf(
        "Submitted with consent acknowledgements:\n"
        . "• Driver consent confirmed by referrer at %s\n"
        . "• Terms & Conditions accepted\n"
        . "• Referrer self-consent for status & payout contact\n"
        . "Submitter IP: %s\n"
        . "User-Agent: %s",
        date('Y-m-d H:i:s T'),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 200)
    );
    $c = $db->prepare('INSERT INTO comments (referral_id, author, body, visible_to_referrer)
        VALUES (?, "SYSTEM", ?, 0)');
    $c->execute([$referralId, $consentBody]);

    if ($notes !== '') {
        $c = $db->prepare('INSERT INTO comments (referral_id, author, body, visible_to_referrer)
            VALUES (?, "SYSTEM", ?, 1)');
        $c->execute([$referralId, 'Note from referrer at submission: ' . $notes]);
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    http_response_code(500);
    echo 'Could not save your referral. Please try again in a moment.';
    exit;
}

// Telegram notification — fire and forget. Never block the user.
try {
    $referrer = [
        'name'  => $referrerName,
        'email' => (string)$referrerEmail,
        'phone' => $referrerPhone,
    ];
    $referral = [
        'id'           => $referralId,
        'driver_name'  => $driverName,
        'driver_email' => (string)$driverEmail,
        'driver_phone' => $driverPhone,
    ];
    $msg = telegram_format_new_referral($config, $referrer, $referral, $notes !== '' ? $notes : null);
    telegram_notify($config, $msg);
} catch (Throwable $e) {
    // Swallow — Telegram failures must not surface to the user.
}

redirect('/thanks.php?id=' . $referralId);
