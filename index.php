<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$bounty = (int)($config['referral_bounty'] ?? 200);
render_public_header($config, '', 'home');
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <p class="eyebrow">Driver Referral Program</p>
      <h1 class="hero-title">Refer a Driver.<br><span class="accent">Get Paid.</span></h1>
      <p class="lead">
        Know a solid CDL driver? Send them our way. We handle recruiting,
        onboarding, and the paperwork. You earn
        <strong class="accent-navy">$<?= number_format($bounty) ?></strong>
        per driver who completes 14 days on the road with OTR Express Group.
      </p>
      <div class="cta-row">
        <a class="btn-primary" href="/refer">Refer a Driver</a>
        <a class="btn-ghost" href="/status">Check Referral Status</a>
      </div>
    </div>
    <div class="card card-brand">
      <p class="eyebrow">How It Works</p>
      <ol class="steps">
        <li><span class="step-num">1</span><div><strong>Submit the driver</strong><p>Name, email, phone. Your contact info too so we can pay you.</p></div></li>
        <li><span class="step-num">2</span><div><strong>Track every step</strong><p>Contacted, application, documents, insurance, orientation — visible in your status page.</p></div></li>
        <li><span class="step-num">3</span><div><strong>Get paid at 14 days</strong><p>Once the driver completes 14 days working, your $<?= number_format($bounty) ?> payout is approved.</p></div></li>
      </ol>
    </div>
  </div>
</section>

<section class="section section-band">
  <div class="wrap">
    <div class="section-head">
      <div>
        <p class="eyebrow">Full Visibility</p>
        <h2>See Every Stage of the Hire</h2>
        <p class="lead narrow">
          Unlike most referral programs, you don't just send a name and hope.
          You see exactly where your driver is in the pipeline and read admin
          notes in real time.
        </p>
      </div>
      <a class="btn-ghost" href="/status">Open Status Tracker</a>
    </div>
    <div class="grid-3">
      <?php foreach (OTR_STATUS_ORDER as $s): if ($s === 'REJECTED') continue; ?>
        <div class="stage-card">
          <p class="stage-title"><?= e(status_label($s)) ?></p>
          <p class="stage-desc"><?= e(status_description($s)) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap grid-2">
    <div class="card">
      <p class="eyebrow">The Rules</p>
      <h3>Simple, Fair, Transparent</h3>
      <ul class="rules">
        <li>Submit the driver through the referral form with their name, email, and phone.</li>
        <li>You confirm the driver has agreed to be contacted by OTR Express Group.</li>
        <li>Admin reviews and moves the referral through each stage: contacted, documents, insurance, orientation.</li>
        <li>If the driver is rejected, you see the reason in your status page.</li>
        <li>When the driver starts, the 14-day clock begins. On day 14 the referral is marked payout-eligible.</li>
        <li>Payout is released once the admin confirms and marks the referral paid.</li>
      </ul>
      <p class="hint" style="margin-top: 1rem;">
        Full program rules are in our <a href="/terms">Terms &amp; Conditions</a>.
      </p>
    </div>
    <div class="card card-brand">
      <p class="eyebrow">Ready?</p>
      <h3>Send Us Your Next Driver</h3>
      <p>It takes less than a minute. You'll get a confirmation and a status link you can check anytime with your email or phone.</p>
      <div class="cta-row">
        <a class="btn-primary" style="background:#fff; color: var(--navy);" href="/refer">Start a Referral</a>
        <a class="btn-ghost" style="color:#fff; border-color:rgba(255,255,255,0.4);" href="/status">I already referred someone</a>
      </div>
    </div>
  </div>
</section>
<?php render_public_footer($config);
