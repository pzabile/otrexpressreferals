<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout-admin.php';

$admin = require_admin($db);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/admin/');
}

$stmt = $db->prepare('SELECT r.*, ref.id AS referrer_id, ref.name AS referrer_name,
    ref.email AS referrer_email, ref.phone AS referrer_phone
    FROM referrals r JOIN referrers ref ON ref.id = r.referrer_id WHERE r.id = ? LIMIT 1');
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) {
    http_response_code(404);
    exit('Referral not found.');
}

$cmts = $db->prepare('SELECT * FROM comments WHERE referral_id = ? ORDER BY created_at DESC');
$cmts->execute([$id]);
$comments = $cmts->fetchAll();

$stages = $db->prepare('SELECT * FROM stage_updates WHERE referral_id = ? ORDER BY created_at ASC');
$stages->execute([$id]);
$stageUpdates = $stages->fetchAll();

$flashOk = flash_get('ok');
$flashErr = flash_get('error');

render_admin_header($config, $admin, 'Referral');

function admin_render_timeline(array $updates, string $currentStatus): void
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
        echo '<li><span class="timeline-dot ' . e($dotClass) . '"></span><div>';
        echo '<span class="' . e($textClass) . '">' . e(status_label($step)) . '</span>';
        if ($update) {
            echo '<span class="timeline-meta">' . e(fmt_date((string)$update['created_at']));
            if (!empty($update['note'])) echo ' — ' . e((string)$update['note']);
            echo '</span>';
        }
        echo '</div></li>';
    }
    if ($isRejected) {
        echo '<li><span class="timeline-dot dot-reject"></span><div><span class="text-reject">' . e(status_label('REJECTED')) . '</span></div></li>';
    }
    echo '</ol>';
}
?>
<section class="wrap section">
  <a class="back-link" href="/admin/">← Back to queue</a>

  <div class="section-head">
    <div>
      <p class="eyebrow">Referral</p>
      <h1><?= e((string)$r['driver_name']) ?></h1>
      <p class="hint">
        Submitted <?= e(fmt_date((string)$r['created_at'])) ?> &middot; Last updated <?= e(fmt_date((string)$r['updated_at'])) ?>
      </p>
    </div>
    <div class="ref-head-right">
      <?= status_badge((string)$r['status']) ?>
      <p class="hint right"><?= e(status_description((string)$r['status'])) ?></p>
    </div>
  </div>

  <?php if ($flashOk): ?><div class="alert alert-ok"><?= e($flashOk) ?></div><?php endif; ?>
  <?php if ($flashErr): ?><div class="alert alert-error"><?= e($flashErr) ?></div><?php endif; ?>

  <div class="detail-grid">
    <aside class="card">
      <h2>Driver</h2>
      <dl class="kv">
        <div><dt>Name</dt><dd><?= e((string)$r['driver_name']) ?></dd></div>
        <div><dt>Email</dt><dd>
          <?php if (!empty($r['driver_email'])): ?>
            <a href="mailto:<?= e((string)$r['driver_email']) ?>"><?= e((string)$r['driver_email']) ?></a>
          <?php else: ?>
            <span class="muted">Not provided</span>
          <?php endif; ?>
        </dd></div>
        <div><dt>Phone</dt><dd><a href="tel:<?= e(phone_digits((string)$r['driver_phone'])) ?>"><?= e(phone_pretty((string)$r['driver_phone'])) ?></a></dd></div>
      </dl>
      <hr class="hr">
      <h2>Referrer</h2>
      <dl class="kv">
        <div><dt>Name</dt><dd><?= e((string)$r['referrer_name']) ?></dd></div>
        <div><dt>Email</dt><dd>
          <?php if (!empty($r['referrer_email'])): ?>
            <a href="mailto:<?= e((string)$r['referrer_email']) ?>"><?= e((string)$r['referrer_email']) ?></a>
          <?php else: ?>
            <span class="muted">Not provided</span>
          <?php endif; ?>
        </dd></div>
        <div><dt>Phone</dt><dd><a href="tel:<?= e(phone_digits((string)$r['referrer_phone'])) ?>"><?= e(phone_pretty((string)$r['referrer_phone'])) ?></a></dd></div>
      </dl>
      <hr class="hr">
      <h2>Payout</h2>
      <dl class="kv">
        <div><dt>Started Working</dt><dd><?= e(fmt_short_date($r['started_working_at'] ?? null)) ?></dd></div>
        <div><dt>14-Day Eligibility</dt><dd><?= e(fmt_short_date($r['payout_eligible_at'] ?? null)) ?></dd></div>
        <div><dt>Paid</dt><dd><?= !empty($r['paid_at']) ? e(fmt_short_date((string)$r['paid_at'])) : 'Pending' ?></dd></div>
      </dl>
      <?php if ($r['status'] === 'TWO_WEEKS_COMPLETED' && empty($r['paid_at'])): ?>
        <form method="post" action="/admin/actions" class="mt">
          <?= csrf_field($config) ?>
          <input type="hidden" name="action" value="mark_paid">
          <input type="hidden" name="referral_id" value="<?= (int)$r['id'] ?>">
          <button class="btn-primary full" type="submit">Mark Referrer Paid</button>
        </form>
      <?php endif; ?>
    </aside>

    <div class="stack">
      <section class="card">
        <h2>Update Stage</h2>
        <p class="hint">
          Moving to <strong class="accent">Started Working</strong> starts the 14-day clock automatically.
          Marking <strong class="accent">Not Eligible for Payout</strong> requires an internal reason for your records — the referrer never sees the text, only the stage label.
        </p>
        <form method="post" action="/admin/actions" class="form-stack">
          <?= csrf_field($config) ?>
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" name="referral_id" value="<?= (int)$r['id'] ?>">
          <label class="field">
            <span class="field-label">New status</span>
            <select name="status" class="field-input">
              <?php foreach (OTR_STATUS_ORDER as $s): ?>
                <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="field">
            <span class="field-label">Internal note (admin-only — not shown to referrer)</span>
            <input name="note" class="field-input" placeholder="e.g. Sent application packet via email">
          </label>
          <label class="field">
            <span class="field-label">Internal reason (required if Not Eligible for Payout)</span>
            <textarea name="rejection_reason" class="field-input field-textarea" placeholder="Why this referral isn't eligible. Admin-only — never shown to the referrer."><?= e((string)($r['rejection_reason'] ?? '')) ?></textarea>
            <span class="hint" style="margin-top:0.4rem">For your records only. The referrer just sees the stage label &ldquo;Not Eligible for Payout&rdquo;.</span>
          </label>
          <div class="form-actions between">
            <p class="hint">Saves a timeline entry. Only the stage label is visible to the referrer; your notes and reasons stay internal.</p>
            <button class="btn-primary" type="submit">Save Update</button>
          </div>
        </form>
      </section>

      <section class="card">
        <h2>Timeline</h2>
        <?php admin_render_timeline($stageUpdates, (string)$r['status']); ?>
        <?php if ($r['status'] === 'REJECTED' && !empty($r['rejection_reason'])): ?>
          <div class="alert">
            <p class="alert-title">Internal reason (admin-only)</p>
            <p><?= nl2br(e((string)$r['rejection_reason'])) ?></p>
          </div>
        <?php endif; ?>
      </section>

      <section class="card">
        <h2>Internal Notes</h2>
        <p class="hint">
          For your tracking only. Nothing in this section is shown to the
          referrer — they only see the stage label and date. Safe to log
          MVR, drug-test, insurance, document, or background-check details
          here for your own records.
        </p>
        <form method="post" action="/admin/actions" class="form-stack">
          <?= csrf_field($config) ?>
          <input type="hidden" name="action" value="add_comment">
          <input type="hidden" name="referral_id" value="<?= (int)$r['id'] ?>">
          <label class="field">
            <span class="field-label">Add a note</span>
            <textarea name="body" class="field-input field-textarea" required placeholder="What you want to remember about this referral."></textarea>
          </label>
          <div class="form-actions right">
            <button class="btn-primary" type="submit">Save Note</button>
          </div>
        </form>

        <ul class="comments mt">
          <?php if (empty($comments)): ?>
            <li class="hint">No notes yet. Add one above.</li>
          <?php endif; ?>
          <?php foreach ($comments as $c): ?>
            <li>
              <div class="comment-meta">
                <span><?= $c['author'] === 'SYSTEM' ? 'System' : 'Admin' ?> &middot; <?= e(fmt_date((string)$c['created_at'])) ?></span>
                <span class="muted">Internal</span>
              </div>
              <p><?= nl2br(e((string)$c['body'])) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
  </div>
</section>
<?php render_admin_footer();
