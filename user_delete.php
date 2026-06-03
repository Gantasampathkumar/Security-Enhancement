<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        header('Location: users.php?msg=' . urlencode('Invalid request.') . '&type=danger');
        exit;
    }

    if ($id > 0 && $id !== (int)$_SESSION['user_id']) {
        $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $stmt->close();
            record_activity($mysqli, (int)$_SESSION['user_id'], 'Delete User');
            header('Location: users.php?msg=' . urlencode('User deleted successfully.') . '&type=success');
            exit;
        }

        $stmt->close();
    }
}

header('Location: users.php?msg=' . urlencode('Unable to delete user.') . '&type=danger');
exit;
?>
