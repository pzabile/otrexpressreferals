<?php
declare(strict_types=1);

function render_admin_header(array $config, ?array $admin, string $title): void
{
    $siteName = e((string)$config['site_name']);
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Admin — <?= $siteName ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body class="site admin">
<header class="admin-bar">
  <div class="wrap admin-bar-inner">
    <div class="admin-bar-left">
      <span class="admin-chip">Admin Console</span>
      <?php if ($admin !== null): ?>
        <nav class="admin-nav">
          <a href="/admin/">Referrals</a>
        </nav>
      <?php endif; ?>
    </div>
    <?php if ($admin !== null): ?>
      <form method="post" action="/admin/logout.php" class="admin-bar-right">
        <?= csrf_field($config) ?>
        <span class="admin-email"><?= e($admin['email']) ?></span>
        <button type="submit" class="btn-text">Sign Out</button>
      </form>
    <?php endif; ?>
  </div>
</header>
<main class="main">
<?php
}

function render_admin_footer(): void
{
    ?>
</main>
</body>
</html><?php
}
