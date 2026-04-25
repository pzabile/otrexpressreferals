<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$referral = null;
if ($id > 0) {
    $stmt = $db->prepare('SELECT r.*, ref.name AS referrer_name
        FROM referrals r JOIN referrers ref ON ref.id = r.referrer_id
        WHERE r.id = ? LIMIT 1');
    $stmt->execute([$id]);
    $referral = $stmt->fetch() ?: null;
}

render_public_header($config, 'Referral Received', '');
?>
<section class="wrap section center">
  <p class="eyebrow">Referral Received</p>
  <h1>You're in the queue.</h1>
  <p class="lead narrow">
    Thanks<?= $referral ? ', ' . e((string)$referral['referrer_name']) : '' ?>.
    Your referral has been sent to the OTR Express admin team. Check back
    anytime to see where <?= $referral ? e((string)$referral['driver_name']) : 'your driver' ?>
    is in the pipeline.
  </p>
  <div class="cta-row center">
    <a class="btn-primary" href="/status.php">Check Referral Status</a>
    <a class="btn-ghost" href="/refer.php">Refer Another Driver</a>
  </div>
</section>
<?php render_public_footer($config);
