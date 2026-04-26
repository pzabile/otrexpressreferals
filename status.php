<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$email = strtolower(trim((string)($_GET['email'] ?? '')));
$phone = trim((string)($_GET['phone'] ?? ''));
$phoneDigits = phone_digits($phone);
$hasQuery = $email !== '' || $phoneDigits !== '';

$referrer = null;
$referrals = [];

if ($hasQuery) {
    if ($email !== '') {
        $s = $db->prepare('SELECT * FROM referrers WHERE email = ? LIMIT 1');
        $s->execute([$email]);
        $referrer = $s->fetch() ?: null;
    }
    if ($referrer === null && $phoneDigits !== '') {
        $all = $db->query('SELECT * FROM referrers')->fetchAll();
        foreach ($all as $r) {
            if (phone_digits((string)$r['phone']) === $phoneDigits) {
                $referrer = $r;
                break;
            }
        }
    }
    if ($referrer !== null) {
        $s = $db->prepare('SELECT * FROM referrals WHERE referrer_id = ? ORDER BY created_at DESC');
        $s->execute([(int)$referrer['id']]);
        $referrals = $s->fetchAll();
        if ($referrals) {
            $ids = array_map(fn($r) => (int)$r['id'], $referrals);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $st = $db->prepare("SELECT * FROM stage_updates
                WHERE referral_id IN ($placeholders)
                ORDER BY created_at ASC");
            $st->execute($ids);
            $stagesByRef = [];
            foreach ($st->fetchAll() as $row) {
                $stagesByRef[(int)$row['referral_id']][] = $row;
            }

            foreach ($referrals as &$r) {
                $r['stage_updates'] = $stagesByRef[(int)$r['id']] ?? [];
            }
            unset($r);
        }
    }
}

render_public_header($config, 'Check Referral Status', 'status');

/**
 * Render a vertical timeline of stages for a referral.
 * Internal stage notes are intentionally NOT shown to the referrer to keep
 * sensitive driver info (documents, medical, MVR, etc.) confidential.
 * Only the stage label and date are displayed.
 */
function render_timeline(array $updates, string $currentStatus): void
{
    $steps = array_values(array_filter(OTR_STATUS_ORDER, fn($s) => $s !== 'REJECTED'));
    $currentIndex = array_search($currentStatus, $steps, true);
    $isRejected = $currentStatus === 'REJECTED';

    $byStatus = [];
    foreach ($updates as $u) {
        $byStatus[$u['status']] = $byStatus[$u['status']] ?? $u;
    }

    echo '<ol class="timeline">';
    foreach ($steps as $i => $step) {
        $reached = !$isRejected && $currentIndex !== false && $currentIndex >= $i;
        $active = !$isRejected && $currentIndex === $i;
        $dotClass = $reached ? ($active ? 'dot-brand' : 'dot-done') : 'dot-pending';
        $textClass = $reached ? 'text-strong' : 'text-faint';
        $update = $byStatus[$step] ?? null;
        echo '<li>';
        echo '<span class="timeline-dot ' . e($dotClass) . '"></span>';
        echo '<div>';
        echo '<span class="' . e($textClass) . '">' . e(status_label($step)) . '</span>';
        if ($update) {
            echo '<span class="timeline-meta">' . e(fmt_date((string)$update['created_at'])) . '</span>';
        }
        echo '</div>';
        echo '</li>';
    }
    if ($isRejected) {
        echo '<li><span class="timeline-dot dot-reject"></span><div><span class="text-reject">' . e(status_label('REJECTED')) . '</span></div></li>';
    }
    echo '</ol>';
}
?>
<section class="wrap section">
  <div class="section-head single">
    <p class="eyebrow">Referral Status</p>
    <h1>Track Your Referrals</h1>
    <p class="lead">
      Enter the email or phone number you used when you referred the driver.
      All your referrals will show up with a high-level timeline of where
      they are in our pipeline.
    </p>
  </div>

  <form class="card lookup-form" method="get">
    <label class="field">
      <span class="field-label">Email used on referral</span>
      <input class="field-input" name="email" type="email" value="<?= e($_GET['email'] ?? '') ?>" placeholder="you@example.com">
    </label>
    <label class="field">
      <span class="field-label">Or phone number</span>
      <input class="field-input" name="phone" type="tel" value="<?= e($_GET['phone'] ?? '') ?>" placeholder="(555) 123-4567">
    </label>
    <div class="form-actions"><button class="btn-primary" type="submit">Look Up</button></div>
  </form>

  <?php if ($hasQuery && $referrer === null): ?>
    <div class="alert alert-error">
      No referrals found for that email or phone. Double-check what you entered, or
      <a href="/refer">submit a new referral</a>.
    </div>
  <?php elseif ($referrer !== null && empty($referrals)): ?>
    <div class="alert">We found your account but no active referrals yet.
      <a href="/refer">Refer a driver →</a></div>
  <?php elseif (!empty($referrals)): ?>
    <div class="stack">
    <?php foreach ($referrals as $r): ?>
      <article class="card">
        <div class="ref-head">
          <div>
            <p class="hint">Referral submitted <?= e(fmt_short_date((string)$r['created_at'])) ?></p>
            <h2><?= e((string)$r['driver_name']) ?></h2>
          </div>
          <div class="ref-head-right">
            <?= status_badge((string)$r['status']) ?>
            <p class="hint right"><?= e(status_description((string)$r['status'])) ?></p>
          </div>
        </div>

        <?php if (!empty($r['started_working_at'])): ?>
          <div class="stats-row">
            <div><span class="stat-label">Started Working</span><span><?= e(fmt_short_date((string)$r['started_working_at'])) ?></span></div>
            <div><span class="stat-label">14-Day Eligibility</span><span><?= e(fmt_short_date($r['payout_eligible_at'] ?? null)) ?></span></div>
            <div><span class="stat-label">Paid Out</span><span><?= !empty($r['paid_at']) ? e(fmt_short_date((string)$r['paid_at'])) : 'Pending' ?></span></div>
          </div>
        <?php endif; ?>

        <div class="timeline-section">
          <p class="eyebrow">Timeline</p>
          <?php render_timeline($r['stage_updates'], (string)$r['status']); ?>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php render_public_footer($config);
