<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout-admin.php';

if (admin_is_logged_in()) {
    redirect('/admin/');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($config, (string)($_POST['_csrf'] ?? ''))) {
        $error = 'Invalid form token. Reload and try again.';
    } else {
        $email = (string)($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $admin = admin_login($db, $email, $password);
        if ($admin === null) {
            $error = 'Invalid credentials.';
        } else {
            redirect('/admin/');
        }
    }
}

render_admin_header($config, null, 'Sign In');
?>
<section class="wrap section narrow-col">
  <p class="eyebrow">Admin Access</p>
  <h1>Sign In</h1>
  <p class="lead">Use the admin credentials you set during install.</p>

  <form method="post" class="card form-stack">
    <?= csrf_field($config) ?>
    <label class="field">
      <span class="field-label">Email</span>
      <input class="field-input" name="email" type="email" required autocomplete="username">
    </label>
    <label class="field">
      <span class="field-label">Password</span>
      <input class="field-input" name="password" type="password" required autocomplete="current-password">
    </label>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <button class="btn-primary" type="submit">Sign In</button>
  </form>
</section>
<?php render_admin_footer();
