<?php
/**
 * One-time setup wizard. Creates tables from sql/schema.sql and an
 * initial admin user. DELETE THIS FILE after success.
 */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout-public.php';

$step = $_POST['step'] ?? 'start';
$errors = [];
$done = false;

function run_schema(PDO $db): void
{
    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    if ($sql === false) {
        throw new RuntimeException('Could not read sql/schema.sql');
    }
    // Split on semicolons that end a statement. The schema uses simple
    // CREATE TABLE statements, no stored procedures, so this is safe.
    $statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql) ?: []));
    foreach ($statements as $stmt) {
        if ($stmt === '') continue;
        $db->exec($stmt);
    }
}

if ($step === 'run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminEmail = clean_email($_POST['admin_email'] ?? '');
    $adminPass  = (string)($_POST['admin_password'] ?? '');
    $adminPassConfirm = (string)($_POST['admin_password_confirm'] ?? '');

    if ($adminEmail === null) {
        $errors[] = 'Admin email is required and must be valid.';
    }
    if (strlen($adminPass) < 8) {
        $errors[] = 'Admin password must be at least 8 characters.';
    }
    if ($adminPass !== $adminPassConfirm) {
        $errors[] = 'Admin passwords do not match.';
    }
    if (($config['app_secret'] ?? '') === 'REPLACE-ME-WITH-A-LONG-RANDOM-STRING-AT-LEAST-32-CHARS'
        || strlen((string)($config['app_secret'] ?? '')) < 32) {
        $errors[] = 'Set app_secret in config.php to a random 32+ character string before installing.';
    }

    if (empty($errors)) {
        try {
            run_schema($db);
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO admin_users (email, password_hash) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)');
            $stmt->execute([$adminEmail, $hash]);
            $done = true;
        } catch (Throwable $e) {
            $errors[] = 'Install failed: ' . $e->getMessage();
        }
    }
}

render_public_header($config, 'Install', '');
?>
<section class="hero hero-sm">
  <div class="wrap">
    <p class="eyebrow">Setup Wizard</p>
    <h1>Install OTR Express Referrals</h1>
    <p class="lead">Creates the database tables and your first admin user.</p>
  </div>
</section>

<section class="wrap section">
<?php if ($done): ?>
  <div class="card card-ok">
    <h2>Installation complete</h2>
    <p>Your admin account has been created.</p>
    <p><strong>Delete <code>install.php</code> from your server now.</strong> Leaving it in place is a security risk.</p>
    <p><a class="btn-primary" href="/admin/login.php">Go to admin login</a></p>
  </div>
<?php else: ?>
  <div class="card">
    <h2>Step 1 — Database credentials</h2>
    <p class="hint">Using the values from <code>config.php</code>:</p>
    <ul class="kv">
      <li><span>Host</span><strong><?= e((string)$config['db_host']) ?>:<?= e((string)$config['db_port']) ?></strong></li>
      <li><span>Database</span><strong><?= e((string)$config['db_name']) ?></strong></li>
      <li><span>User</span><strong><?= e((string)$config['db_user']) ?></strong></li>
    </ul>
    <p class="hint">If any of these are wrong, edit <code>config.php</code> and reload this page.</p>
  </div>

  <div class="card">
    <h2>Step 2 — Create your admin user</h2>
    <p class="hint">This is the account you'll use to log into <code>/admin/</code>.</p>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
      </div>
    <?php endif; ?>
    <form method="post" class="form-grid">
      <input type="hidden" name="step" value="run">
      <label class="field">
        <span class="field-label">Admin email</span>
        <input class="field-input" type="email" name="admin_email" required value="<?= e($_POST['admin_email'] ?? '') ?>">
      </label>
      <label class="field">
        <span class="field-label">Admin password (min 8)</span>
        <input class="field-input" type="password" name="admin_password" required minlength="8">
      </label>
      <label class="field">
        <span class="field-label">Confirm password</span>
        <input class="field-input" type="password" name="admin_password_confirm" required minlength="8">
      </label>
      <div class="form-actions">
        <button type="submit" class="btn-primary">Run Install</button>
      </div>
    </form>
  </div>
<?php endif; ?>
</section>
<?php render_public_footer($config);
