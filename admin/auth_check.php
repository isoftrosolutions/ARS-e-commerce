<?php
// admin/auth_check.php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', 'Access denied. Admin only.', 'danger');
}
