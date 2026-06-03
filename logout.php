<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

logout_user($mysqli);
header('Location: login.php?msg=' . urlencode('You have been logged out.'));
exit;
?>
