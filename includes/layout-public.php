<?php
declare(strict_types=1);

/**
 * Render the public site shell. Call render_public_header() at the top
 * of a page and render_public_footer() at the bottom.
 */

function render_public_header(array $config, string $title, string $active = ''): void
{
    $siteName = e((string)$config['site_name']);
    $pageTitle = $title === '' ? $siteName : e($title) . ' — ' . $siteName;
    $isActive = fn(string $name) => $active === $name ? ' class="active"' : '';
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $pageTitle ?></title>
<meta name="description" content="Refer a CDL driver to OTR Express Group. Track every step from referral to hire and get paid when your driver completes 14 days.">
<link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
<link rel="alternate icon" href="/assets/favicon.svg">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body class="site">
<header class="nav">
  <div class="nav-inner">
    <a class="brand" href="/" aria-label="OTR Express Group home">
      <span class="brand-text">
        <span class="brand-otr">OTR EXPRESS</span>
        <span class="brand-group">GROUP</span>
      </span>
    </a>
    <nav class="nav-links" aria-label="Primary">
      <a href="/"<?= $isActive('home') ?>>Home</a>
      <a href="/refer"<?= $isActive('refer') ?>>Refer a Driver</a>
      <a href="/status"<?= $isActive('status') ?>>Check Status</a>
      <a href="/terms"<?= $isActive('terms') ?>>Terms</a>
      <a class="nav-cta" href="/refer">Refer Now</a>
    </nav>
    <details class="nav-mobile">
      <summary aria-label="Open menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="4" y1="6"  x2="20" y2="6"></line>
          <line x1="4" y1="12" x2="20" y2="12"></line>
          <line x1="4" y1="18" x2="20" y2="18"></line>
        </svg>
      </summary>
      <div class="nav-mobile-menu">
        <a href="/">Home</a>
        <a href="/refer">Refer a Driver</a>
        <a href="/status">Check Status</a>
        <a href="/terms">Terms</a>
      </div>
    </details>
  </div>
</header>
<main class="main">
<?php
}

function render_public_footer(array $config): void
{
    $year = date('Y');
    ?>
</main>
<footer class="footer">
  <div class="wrap">
    <div class="footer-inner">
      <div>
        <span class="brand-text">
          <span class="brand-otr">OTR EXPRESS</span>
          <span class="brand-group">GROUP</span>
        </span>
        <p class="hint" style="margin-top: 0.5rem;">Driver Referrals Program</p>
      </div>
      <div class="footer-cols">
        <div class="footer-col">
          <h4>Program</h4>
          <a href="/refer">Refer a Driver</a>
          <a href="/status">Check Status</a>
          <a href="/terms">Terms &amp; Conditions</a>
        </div>
        <div class="footer-col">
          <h4>Company</h4>
          <a href="https://otrexpressgroup.com/" target="_blank" rel="noopener">OTR Express Group</a>
          <a href="mailto:info@otrexpressgroup.com">info@otrexpressgroup.com</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?= $year ?> Benux Corp dba OTR Express Group. All rights reserved.
    </div>
  </div>
</footer>
</body>
</html><?php
}
