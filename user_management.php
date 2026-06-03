<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
header('Location: users.php');
exit;
?>
