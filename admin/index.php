<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout-admin.php';

$admin = require_admin($db);

$filterStatus = (string)($_GET['status'] ?? '');
$query = trim((string)($_GET['q'] ?? ''));

$where = [];
$params = [];
if ($filterStatus !== '' && is_valid_status($filterStatus)) {
    $where[] = 'r.status = ?';
    $params[] = $filterStatus;
}
if ($query !== '') {
    $like = '%' . $query . '%';
    $where[] = '(r.driver_name LIKE ? OR r.driver_email LIKE ? OR r.driver_phone LIKE ?
                OR ref.name LIKE ? OR ref.email LIKE ? OR ref.phone LIKE ?)';
    array_push($params, $like, $like, $like, $like, $like, $like);
}

$sql = 'SELECT r.*, ref.name AS referrer_name, ref.email AS referrer_email, ref.phone AS referrer_phone,
        (SELECT COUNT(*) FROM comments c WHERE c.referral_id = r.id) AS comment_count
        FROM referrals r JOIN referrers ref ON ref.id = r.referrer_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$referrals = $stmt->fetchAll();

$counts = [];
foreach ($db->query('SELECT status, COUNT(*) AS c FROM referrals GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int)$row['c'];
}
$get = fn(string $k): int => $counts[$k] ?? 0;
$total = array_sum($counts);
$inProgress = $get('CONTACTED') + $get('APPLICATION_SENT') + $get('WAITING_ON_DOCUMENTS')
            + $get('WAITING_ON_INSURANCE') + $get('ORIENTATION_SCHEDULED') + $get('STARTED_WORKING');

render_admin_header($config, $admin, 'Referrals');
?>
<section class="wrap section">
  <div class="section-head">
    <div>
      <p class="eyebrow">Referrals Queue</p>
      <h1>All Driver Referrals</h1>
      <p class="hint">
        <?= count($referrals) ?> shown<?= $filterStatus !== '' ? ' — filtered by ' . e(status_label($filterStatus)) : '' ?>
      </p>
    </div>
    <form class="filter-form" method="get">
      <input class="field-input" name="q" value="<?= e($query) ?>" placeholder="Search driver or referrer…">
      <select class="field-input" name="status">
        <option value="">All statuses</option>
        <?php foreach (OTR_STATUS_ORDER as $s): ?>
          <option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn-primary" type="submit">Filter</button>
    </form>
  </div>

  <div class="stat-grid">
    <div class="stat-card"><p>Total</p><strong><?= $total ?></strong></div>
    <div class="stat-card"><p>New</p><strong><?= $get('SUBMITTED') ?></strong></div>
    <div class="stat-card"><p>In Progress</p><strong><?= $inProgress ?></strong></div>
    <div class="stat-card accent"><p>Payout Ready</p><strong><?= $get('TWO_WEEKS_COMPLETED') ?></strong></div>
    <div class="stat-card"><p>Paid</p><strong><?= $get('HIRED_PAID') ?></strong></div>
  </div>

  <div class="card no-pad">
    <table class="table">
      <thead>
        <tr>
          <th>Driver</th>
          <th>Referrer</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Notes</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($referrals)): ?>
          <tr><td colspan="6" class="empty">No referrals match this filter yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($referrals as $r): ?>
          <tr>
            <td>
              <p class="strong"><?= e((string)$r['driver_name']) ?></p>
              <?php if (!empty($r['driver_email'])): ?>
                <p class="hint"><?= e((string)$r['driver_email']) ?></p>
              <?php endif; ?>
              <p class="hint"><?= e(phone_pretty((string)$r['driver_phone'])) ?></p>
            </td>
            <td>
              <p><?= e((string)$r['referrer_name']) ?></p>
              <?php if (!empty($r['referrer_email'])): ?>
                <p class="hint"><?= e((string)$r['referrer_email']) ?></p>
              <?php endif; ?>
              <p class="hint"><?= e(phone_pretty((string)$r['referrer_phone'])) ?></p>
            </td>
            <td><?= status_badge((string)$r['status']) ?></td>
            <td class="hint"><?= e(fmt_short_date((string)$r['created_at'])) ?></td>
            <td class="hint"><?= (int)$r['comment_count'] ?> note<?= (int)$r['comment_count'] === 1 ? '' : 's' ?></td>
            <td class="right"><a class="btn-ghost sm" href="/admin/referral?id=<?= (int)$r['id'] ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php render_admin_footer();
