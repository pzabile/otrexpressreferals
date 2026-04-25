<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($config, (string)($_POST['_csrf'] ?? ''))) {
    admin_logout();
}
redirect('/admin/login');
