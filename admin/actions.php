<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$admin = require_admin($db);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

if (!csrf_check($config, (string)($_POST['_csrf'] ?? ''))) {
    http_response_code(400);
    exit('Invalid form token. Reload and try again.');
}

$action = (string)($_POST['action'] ?? '');
$referralId = (int)($_POST['referral_id'] ?? 0);
if ($referralId <= 0) {
    http_response_code(400);
    exit('Missing referral.');
}

$stmt = $db->prepare('SELECT * FROM referrals WHERE id = ? LIMIT 1');
$stmt->execute([$referralId]);
$referral = $stmt->fetch();
if (!$referral) {
    http_response_code(404);
    exit('Referral not found.');
}

switch ($action) {
    case 'update_status':
        $status = (string)($_POST['status'] ?? '');
        $note = trim((string)($_POST['note'] ?? ''));
        $rejectionReason = trim((string)($_POST['rejection_reason'] ?? ''));
        if (!is_valid_status($status)) {
            http_response_code(400);
            exit('Unknown status.');
        }
        if ($status === 'REJECTED' && $rejectionReason === '') {
            flash_set('error', 'A rejection reason is required when marking a referral as Rejected.');
            redirect('/admin/referral?id=' . $referralId);
        }

        $updates = ['status = ?'];
        $params  = [$status];

        if ($status === 'REJECTED') {
            $updates[] = 'rejection_reason = ?';
            $params[] = $rejectionReason;
        } else {
            $updates[] = 'rejection_reason = NULL';
        }

        if ($status === 'STARTED_WORKING' && empty($referral['started_working_at'])) {
            $updates[] = 'started_working_at = NOW()';
            $updates[] = 'payout_eligible_at = DATE_ADD(NOW(), INTERVAL 14 DAY)';
        }
        if ($status === 'TWO_WEEKS_COMPLETED' && empty($referral['payout_eligible_at'])) {
            $updates[] = 'payout_eligible_at = NOW()';
        }
        if ($status === 'HIRED_PAID' && empty($referral['paid_at'])) {
            $updates[] = 'paid_at = NOW()';
        }

        $params[] = $referralId;
        $sql = 'UPDATE referrals SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $db->prepare($sql)->execute($params);

        $timelineNote = $status === 'REJECTED' && $rejectionReason !== ''
            ? 'Rejected: ' . $rejectionReason
            : ($note !== '' ? $note : null);

        $db->prepare('INSERT INTO stage_updates (referral_id, status, note) VALUES (?, ?, ?)')
            ->execute([$referralId, $status, $timelineNote]);

        flash_set('ok', 'Status updated.');
        break;

    case 'add_comment':
        $body = trim((string)($_POST['body'] ?? ''));
        $visible = isset($_POST['visible_to_referrer']) ? 1 : 0;
        if ($body === '') {
            flash_set('error', 'Comment cannot be empty.');
            redirect('/admin/referral?id=' . $referralId);
        }
        $db->prepare('INSERT INTO comments (referral_id, author, body, visible_to_referrer)
            VALUES (?, "ADMIN", ?, ?)')
            ->execute([$referralId, $body, $visible]);
        flash_set('ok', 'Comment added.');
        break;

    case 'delete_comment':
        $commentId = (int)($_POST['comment_id'] ?? 0);
        if ($commentId <= 0) {
            flash_set('error', 'Missing comment id.');
            redirect('/admin/referral?id=' . $referralId);
        }
        $del = $db->prepare('DELETE FROM comments WHERE id = ? AND referral_id = ?');
        $del->execute([$commentId, $referralId]);
        flash_set('ok', 'Note deleted.');
        break;

    case 'set_share_consent':
        $value = (string)($_POST['share_with_referrer'] ?? '');
        if (!in_array($value, ['PENDING', 'YES', 'NO'], true)) {
            flash_set('error', 'Invalid consent value.');
            redirect('/admin/referral?id=' . $referralId);
        }
        $db->prepare('UPDATE referrals SET share_with_referrer = ?, share_consent_at = NOW() WHERE id = ?')
            ->execute([$value, $referralId]);
        $note = match ($value) {
            'YES' => 'Driver consented to share pipeline status with referrer.',
            'NO'  => 'Driver declined to share pipeline status with referrer; referrer view restricted.',
            default => 'Driver share-consent reset to pending.',
        };
        $db->prepare('INSERT INTO comments (referral_id, author, body, visible_to_referrer) VALUES (?, "SYSTEM", ?, 0)')
            ->execute([$referralId, $note]);
        flash_set('ok', 'Driver share consent saved.');
        break;

    case 'mark_paid':
        $db->prepare('UPDATE referrals SET status = "HIRED_PAID", paid_at = NOW() WHERE id = ?')
            ->execute([$referralId]);
        $db->prepare('INSERT INTO stage_updates (referral_id, status, note) VALUES (?, "HIRED_PAID", ?)')
            ->execute([$referralId, 'Referrer payout recorded.']);
        flash_set('ok', 'Marked as paid.');
        break;

    default:
        http_response_code(400);
        exit('Unknown action.');
}

redirect('/admin/referral?id=' . $referralId);
