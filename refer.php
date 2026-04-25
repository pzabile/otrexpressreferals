<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$bounty = (int)($config['referral_bounty'] ?? 500);
$old = $_SESSION['_old_refer'] ?? [];
$errors = $_SESSION['_errors_refer'] ?? [];
unset($_SESSION['_old_refer'], $_SESSION['_errors_refer']);

render_public_header($config, 'Refer a Driver', 'refer');
?>
<section class="wrap section">
  <div class="section-head single">
    <p class="eyebrow">Refer a Driver</p>
    <h1>Submit Your Referral</h1>
    <p class="lead">
      Tell us who you're referring and how we can reach you. Your submission
      goes straight to the admin queue. You'll earn $<?= number_format($bounty) ?>
      once the driver completes 14 days.
    </p>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/refer-submit.php" class="form-stack">
    <?= csrf_field($config) ?>

    <div class="card">
      <h2>Your Info (Referrer)</h2>
      <p class="hint">We need this so we can pay you and send you status updates.</p>
      <div class="form-grid">
        <label class="field">
          <span class="field-label">Your full name *</span>
          <input class="field-input" name="referrer_name" required value="<?= e($old['referrer_name'] ?? '') ?>">
        </label>
        <label class="field">
          <span class="field-label">Your phone *</span>
          <input class="field-input" name="referrer_phone" type="tel" required value="<?= e($old['referrer_phone'] ?? '') ?>">
        </label>
        <label class="field field-wide">
          <span class="field-label">Your email *</span>
          <input class="field-input" name="referrer_email" type="email" required value="<?= e($old['referrer_email'] ?? '') ?>">
        </label>
      </div>
    </div>

    <div class="card">
      <h2>Driver You're Referring</h2>
      <p class="hint">Give us the driver's full legal name, email, and best phone number.</p>
      <div class="form-grid">
        <label class="field">
          <span class="field-label">Driver full name *</span>
          <input class="field-input" name="driver_name" required value="<?= e($old['driver_name'] ?? '') ?>">
        </label>
        <label class="field">
          <span class="field-label">Driver phone *</span>
          <input class="field-input" name="driver_phone" type="tel" required value="<?= e($old['driver_phone'] ?? '') ?>">
        </label>
        <label class="field field-wide">
          <span class="field-label">Driver email *</span>
          <input class="field-input" name="driver_email" type="email" required value="<?= e($old['driver_email'] ?? '') ?>">
        </label>
        <label class="field field-wide">
          <span class="field-label">Notes (optional)</span>
          <textarea class="field-input field-textarea" name="notes" placeholder="CDL class, years of experience, best time to call, anything we should know…"><?= e($old['notes'] ?? '') ?></textarea>
        </label>
      </div>
    </div>

    <div class="form-actions between">
      <p class="hint">By submitting you confirm the driver has agreed to be contacted by OTR Express.</p>
      <button type="submit" class="btn-primary">Submit Referral</button>
    </div>
  </form>
</section>
<?php render_public_footer($config);
