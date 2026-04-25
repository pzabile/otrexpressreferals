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
<meta name="description" content="Refer a CDL driver to OTR Express. Track every step from referral to hire and get paid when your driver completes 14 days.">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body class="site">
<header class="nav">
  <div class="wrap nav-inner">
    <a class="brand" href="/">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v8H3z"/><path d="M14 10h4l3 3v2h-7z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
      </span>
      <span class="brand-text">
        <span class="brand-name">OTR Express</span>
        <span class="brand-sub">Driver Referrals</span>
      </span>
    </a>
    <nav class="nav-links">
      <a href="/"<?= $isActive('home') ?>>Home</a>
      <a href="/refer.php"<?= $isActive('refer') ?>>Refer a Driver</a>
      <a href="/status.php"<?= $isActive('status') ?>>Check Status</a>
      <a class="nav-admin" href="/admin/">Admin</a>
    </nav>
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
  <div class="wrap footer-inner">
    <div>
      <p class="brand-name">OTR Express</p>
      <p class="brand-sub">Driver Referrals Program</p>
    </div>
    <div class="footer-right">
      <p>&copy; <?= $year ?> OTR Express Group. All rights reserved.</p>
      <p class="hint">Refer. Track. Get paid when your driver runs 14 days.</p>
    </div>
  </div>
</footer>
</body>
</html><?php
}
