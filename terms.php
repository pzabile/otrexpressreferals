<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$bounty   = (int)($config['referral_bounty'] ?? 200);
$entity   = (string)($config['legal_entity'] ?? 'Benux Corp');
$dba      = (string)($config['legal_dba'] ?? 'OTR Express Group');
$email    = (string)($config['legal_email'] ?? 'info@otrexpressgroup.com');
$state    = (string)($config['legal_state'] ?? 'Florida');
$siteHost = parse_url((string)($config['site_url'] ?? ''), PHP_URL_HOST) ?: 'referrals.otrexpressgroup.com';
$updated  = 'April 25, 2026';

render_public_header($config, 'Terms & Conditions', 'terms');
?>
<section class="hero hero-sm">
  <div class="wrap">
    <p class="eyebrow">Legal</p>
    <h1>Driver Referral Program — Terms &amp; Conditions</h1>
    <p class="lead narrow">
      These terms govern the OTR Express Group Driver Referral Program
      operated by <?= e($entity) ?> (d/b/a <?= e($dba) ?>). Please read
      them carefully before submitting a referral.
    </p>
    <p class="hint">Last updated: <?= e($updated) ?></p>
  </div>
</section>

<section class="wrap section">
  <div class="legal">

    <div class="toc">
      <strong>Contents</strong>
      <ol>
        <li><a href="#acceptance">Acceptance of Terms</a></li>
        <li><a href="#definitions">Definitions</a></li>
        <li><a href="#eligibility">Eligibility</a></li>
        <li><a href="#program-rules">Referral Program Rules</a></li>
        <li><a href="#payout">Payout, Timing, and Taxes</a></li>
        <li><a href="#disqualification">Disqualification &amp; No Guarantee of Hire</a></li>
        <li><a href="#consent">Driver Consent &amp; TCPA</a></li>
        <li><a href="#communications">How We Will Contact You and the Driver</a></li>
        <li><a href="#privacy">Privacy &amp; Data Use</a></li>
        <li><a href="#intellectual">Intellectual Property</a></li>
        <li><a href="#warranties">Disclaimers &amp; Limitation of Liability</a></li>
        <li><a href="#indemnity">Indemnification</a></li>
        <li><a href="#changes">Changes to the Program</a></li>
        <li><a href="#termination">Termination</a></li>
        <li><a href="#disputes">Governing Law &amp; Disputes</a></li>
        <li><a href="#contact">Contact</a></li>
      </ol>
    </div>

    <h2 id="acceptance">1. Acceptance of Terms</h2>
    <p>
      By submitting a referral through <?= e($siteHost) ?> (the "Site"),
      you ("Referrer", "you") agree to be bound by these Terms &amp;
      Conditions ("Terms") and our use of the information you provide as
      described here. If you do not agree, do not use the Site.
    </p>

    <h2 id="definitions">2. Definitions</h2>
    <ul>
      <li><strong>Company</strong> means <?= e($entity) ?>, doing business as <?= e($dba) ?>.</li>
      <li><strong>Program</strong> means the OTR Express Group Driver Referral Program described on the Site.</li>
      <li><strong>Referrer</strong> means the individual who submits a referral.</li>
      <li><strong>Referred Driver</strong> means the commercial driver whose contact information you submit.</li>
      <li><strong>Qualifying Hire</strong> means a Referred Driver who is hired by the Company and completes 14 consecutive days of paid work.</li>
      <li><strong>Payout</strong> means the referral bonus described in Section 5.</li>
    </ul>

    <h2 id="eligibility">3. Eligibility</h2>
    <ul>
      <li>You must be at least 18 years old.</li>
      <li>You must be legally able to receive payment in the United States.</li>
      <li>Current employees and direct contractors of the Company are eligible only if their offer letter or contract permits referral bonuses.</li>
      <li>You may not refer yourself, an immediate family member living in your household, or anyone whose information you do not have permission to share.</li>
      <li>You may not refer a Referred Driver who is already in the Company's recruiting pipeline at the time of submission.</li>
    </ul>

    <h2 id="program-rules">4. Referral Program Rules</h2>
    <ol>
      <li>You submit the Referred Driver's full legal name, email address, and best phone number through the Site, along with your own contact information.</li>
      <li>You confirm at submission that the Referred Driver has agreed to be contacted by the Company about a job opportunity (see Section 7).</li>
      <li>The Company reviews the referral and updates its status in the pipeline (Submitted, Contacted, Application Sent, Waiting on Documents, Waiting on Insurance, Orientation Scheduled, Started Working, 14 Days Completed, Hired &amp; Paid, or Rejected).</li>
      <li>You can check the live status of every referral you submit at <code>/status.php</code> using the email or phone you used to submit.</li>
      <li>If the referral is Rejected, the reason is shown on your status page.</li>
      <li>Only the first Referrer to submit a particular Referred Driver is credited. Duplicate or subsequent referrals of the same driver do not qualify, regardless of who submits them.</li>
      <li>The Company has sole discretion over whether to hire a Referred Driver. Submitting a referral does not create any obligation to hire.</li>
    </ol>

    <h2 id="payout">5. Payout, Timing, and Taxes</h2>
    <ul>
      <li>The Payout amount is <strong>$<?= number_format($bounty) ?> USD</strong> per Qualifying Hire, payable to the eligible Referrer.</li>
      <li>The 14-day clock starts on the Referred Driver's first day of paid work for the Company. Days off, unpaid leave, or termination during this period reset or end the clock at the Company's discretion.</li>
      <li>The Payout becomes payable after the Referred Driver completes 14 consecutive paid days. The Company will mark the referral "14 Days Completed" and then process payment.</li>
      <li>Payment is issued within 30 days of the referral being marked "14 Days Completed", by check, ACH, or another method the Company designates.</li>
      <li>If your total Payouts in a calendar year exceed the IRS reporting threshold (currently $600), the Company will issue you a Form 1099 and you are responsible for any taxes owed. You agree to provide a completed Form W-9 before payment.</li>
      <li>The Company will not issue a Payout if any of the events in Section 6 occur.</li>
    </ul>

    <h2 id="disqualification">6. Disqualification &amp; No Guarantee of Hire</h2>
    <p>You will not receive a Payout if any of the following occur:</p>
    <ul>
      <li>The Referred Driver does not complete 14 consecutive paid days of work.</li>
      <li>The Referred Driver was already in the Company's pipeline before your submission.</li>
      <li>You provided false, misleading, or incomplete information.</li>
      <li>You did not actually have permission to share the Referred Driver's contact information.</li>
      <li>The Referred Driver opts out of contact at any point before being hired.</li>
      <li>You or the Referred Driver violate these Terms or any applicable law.</li>
    </ul>
    <p>
      Submitting a referral does not guarantee the Referred Driver will be
      hired. The Company evaluates every applicant on its own merits, including
      driving record, MVR, insurance underwriting, drug testing, and operational
      need.
    </p>

    <h2 id="consent">7. Driver Consent &amp; TCPA</h2>
    <p>
      The Telephone Consumer Protection Act ("TCPA") and various state laws
      regulate how individuals may be contacted. You represent and warrant
      that, before submitting a referral:
    </p>
    <ul>
      <li>You have personally spoken with or messaged the Referred Driver about this opportunity;</li>
      <li>The Referred Driver has agreed to be contacted by the Company by phone, text, and email regarding this job opportunity; and</li>
      <li>You have authority to share the Referred Driver's name, email, and phone number with the Company for this purpose.</li>
    </ul>
    <p>
      The Company will, at first contact with the Referred Driver, identify
      itself, state where it obtained the driver's contact information,
      describe the purpose of the contact, and offer the driver an opportunity
      to opt out. The Company will honor any opt-out request promptly.
    </p>
    <p>
      You agree to indemnify and hold the Company harmless from any claim
      arising from your referral, including but not limited to TCPA claims, if
      it turns out the Referred Driver had not in fact agreed to be contacted.
    </p>

    <h2 id="communications">8. How We Will Contact You and the Driver</h2>
    <ul>
      <li>The Company will contact the Referred Driver by phone, text, or email using the contact information you provided. First contact will be manually placed by a recruiter, not via an autodialer or pre-recorded message.</li>
      <li>The Company will contact you at the phone and email you provided to send status updates and arrange Payout. By submitting a referral you consent to receive these communications.</li>
      <li>You may opt out of further status communications by emailing <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>. Opting out forfeits any pending Payouts that depend on contacting you.</li>
      <li>Standard message and data rates from your wireless carrier may apply.</li>
    </ul>

    <h2 id="privacy">9. Privacy &amp; Data Use</h2>
    <ul>
      <li>The Company collects the names, emails, and phone numbers you submit, along with technical metadata (IP address and User-Agent string) to maintain a record of consent.</li>
      <li>The Company uses this information solely to operate the Program: to contact the Referred Driver about a job, update you on referral status, and pay any earned Payout.</li>
      <li>The Company does not sell your or the Referred Driver's personal information.</li>
      <li>The Company retains referral records for as long as needed for legal, tax, and operational purposes, and otherwise as required by law.</li>
      <li>You and the Referred Driver may request deletion of personal data by emailing <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>, subject to the Company's record-retention obligations.</li>
    </ul>

    <h2 id="intellectual">10. Intellectual Property</h2>
    <p>
      All content on the Site, including the Company's logo, trade names,
      trademarks, and the design of the Site, is owned by the Company and may
      not be used without prior written permission. Nothing in these Terms
      grants you any license to the Company's intellectual property.
    </p>

    <h2 id="warranties">11. Disclaimers &amp; Limitation of Liability</h2>
    <p>
      The Site and the Program are provided "as is" without warranty of any
      kind, express or implied, including any warranty of merchantability,
      fitness for a particular purpose, or non-infringement. The Company does
      not guarantee uninterrupted access, error-free operation, or the
      accuracy of any data displayed.
    </p>
    <p>
      To the fullest extent permitted by law, the Company will not be liable
      for any indirect, incidental, consequential, special, or punitive
      damages, or any lost profits or revenues, arising from or related to the
      Program, even if the Company has been advised of the possibility of
      such damages. The Company's total aggregate liability under these Terms
      will not exceed the total Payouts paid or payable to you in the prior
      twelve (12) months, or one hundred U.S. dollars ($100), whichever is
      greater.
    </p>

    <h2 id="indemnity">12. Indemnification</h2>
    <p>
      You agree to indemnify, defend, and hold harmless the Company and its
      officers, directors, employees, and agents from and against any and all
      claims, liabilities, damages, losses, and expenses (including reasonable
      attorneys' fees) arising out of or in any way connected with: (a) your
      use of the Site or the Program; (b) your breach of these Terms; (c) any
      misrepresentation regarding the Referred Driver's consent; or (d) your
      violation of any applicable law.
    </p>

    <h2 id="changes">13. Changes to the Program</h2>
    <p>
      The Company may modify, suspend, or discontinue the Program or these
      Terms at any time, with or without notice. Continued use of the Site
      after a change becomes effective constitutes acceptance of the change.
      Pending Payouts under the previous Terms will be honored only at the
      Company's reasonable discretion.
    </p>

    <h2 id="termination">14. Termination</h2>
    <p>
      The Company may suspend or terminate your participation in the Program
      at any time for any reason, including suspected fraud, abuse, or
      violation of these Terms. Termination forfeits any pending Payouts.
    </p>

    <h2 id="disputes">15. Governing Law &amp; Disputes</h2>
    <p>
      These Terms are governed by the laws of the State of <?= e($state) ?>,
      without regard to conflict-of-law principles. Any dispute arising out
      of or relating to these Terms or the Program will be brought
      exclusively in the state or federal courts located in <?= e($state) ?>,
      and you consent to the personal jurisdiction of those courts.
    </p>
    <p>
      You and the Company waive any right to a jury trial and to participate
      in a class or collective action with respect to any dispute under these
      Terms, to the maximum extent permitted by law.
    </p>

    <h2 id="contact">16. Contact</h2>
    <p>
      Questions about these Terms or the Program can be sent to
      <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> or by mail to
      the Company at the address on file with the State of <?= e($state) ?>
      Department of State.
    </p>

    <hr class="hr">

    <p class="hint">
      <strong>Note:</strong> These Terms are a template tailored to a small
      driver-referral program. They are not legal advice. Have an attorney
      review and customize them for your specific business, state, and
      operational practices before you go live.
    </p>
  </div>
</section>
<?php render_public_footer($config);
