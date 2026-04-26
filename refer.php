<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$bounty = (int)($config['referral_bounty'] ?? 200);
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

  <form method="post" action="/refer-submit" class="form-stack">
    <?= csrf_field($config) ?>

    <div class="card">
      <h2>Your Info (Referrer)</h2>
      <p class="hint">We need this so we can pay you and send you status updates. Phone is required; email is optional but lets us reach you with payment details.</p>
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
          <span class="field-label">Your email (optional)</span>
          <input class="field-input" name="referrer_email" type="email" value="<?= e($old['referrer_email'] ?? '') ?>">
        </label>
      </div>
    </div>

    <div class="card">
      <h2>Driver You're Referring</h2>
      <p class="hint">Give us the driver's full legal name and best phone number. Email is optional.</p>
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
          <span class="field-label">Driver email (optional)</span>
          <input class="field-input" name="driver_email" type="email" value="<?= e($old['driver_email'] ?? '') ?>">
        </label>
        <label class="field field-wide">
          <span class="field-label">Notes (optional)</span>
          <textarea class="field-input field-textarea" name="notes" placeholder="CDL class, years of experience, best time to call, anything we should know…"><?= e($old['notes'] ?? '') ?></textarea>
        </label>
      </div>
    </div>

    <div class="card">
      <h2>Consent &amp; Authorization</h2>
      <p class="hint">
        Required by the TCPA and our Terms. The driver must have agreed to
        be contacted before you submit their information.
      </p>
      <div class="disclosure">
        <p class="disclosure-title">What we share with you about your referral</p>
        <p>
          By participating in the referral program, you understand that OTR
          Express Group may provide limited referral-status updates to the
          person who submitted the referral. These updates may include
          whether the referral was received, contacted, in progress, under
          carrier review, scheduled for orientation, started working, payout
          eligible, paid, or not eligible for payout. OTR Express Group will
          not share private applicant details, documents, medical
          information, background results, insurance details, or specific
          rejection reasons with the referrer.
        </p>
      </div>
      <div class="consent-block">
        <label class="checkbox">
          <input type="checkbox" name="consent_driver" value="1" required <?= !empty($old['consent_driver']) ? 'checked' : '' ?>>
          <span>
            <strong>I have spoken with this driver</strong> and they have agreed
            to be contacted by OTR Express Group (Benux Corp) by phone, text,
            and email about a driving job opportunity. I understand OTR Express
            Group will identify themselves and offer the driver an opt-out at
            first contact.
          </span>
        </label>
        <label class="checkbox">
          <input type="checkbox" name="consent_terms" value="1" required <?= !empty($old['consent_terms']) ? 'checked' : '' ?>>
          <span>
            I have read and agree to the
            <a href="/terms" target="_blank" rel="noopener">Terms &amp; Conditions</a>
            of the Driver Referral Program, including the payout rules and
            disqualification criteria.
          </span>
        </label>
        <label class="checkbox">
          <input type="checkbox" name="consent_self" value="1" required <?= !empty($old['consent_self']) ? 'checked' : '' ?>>
          <span>
            I consent to OTR Express Group contacting <strong>me</strong> at the
            phone (and email, if provided) above to send updates on this referral
            and to arrange payment if it qualifies.
          </span>
        </label>
      </div>
    </div>

    <div class="form-actions between">
      <p class="hint">
        Submitting this form does not guarantee the driver will be hired.
        Payouts are made only after the driver completes 14 days of work.
      </p>
      <button type="submit" class="btn-primary">Submit Referral</button>
    </div>
  </form>
</section>
<?php render_public_footer($config);
