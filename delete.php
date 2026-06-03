<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        header('Location: index.php?msg=' . urlencode('Invalid request.') . '&type=danger');
        exit;
    }

    if ($id > 0) {
        $stmt = $mysqli->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $stmt->close();
            record_activity($mysqli, (int)$_SESSION['user_id'], 'Delete Post');
            header('Location: index.php?msg=' . urlencode('Post deleted successfully.') . '&type=success');
            exit;
        }

        $stmt->close();
    }
}

header('Location: index.php?msg=' . urlencode('Unable to delete post.') . '&type=danger');
exit;
?>
