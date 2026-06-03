<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireEditorOrAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$title = '';
$content = '';
$author = '';

if ($id <= 0) {
    header('Location: index.php?msg=' . urlencode('Invalid post ID.') . '&type=danger');
    exit;
}

if (!canEditPost($mysqli, $id)) {
    header('Location: access_denied.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $errors = array_merge($errors, validate_post_form($title, $content, $author));

    if (!$errors) {
        $stmt = $mysqli->prepare('UPDATE posts SET title = ?, content = ?, author = ? WHERE id = ?');
        $stmt->bind_param('sssi', $title, $content, $author, $id);

        if ($stmt->execute()) {
            $stmt->close();
            record_activity($mysqli, (int)$_SESSION['user_id'], 'Edit Post');
            header('Location: index.php?msg=' . urlencode('Post updated successfully.') . '&type=success');
            exit;
        }

        $errors[] = 'Unable to update post. Please try again.';
        $stmt->close();
    }
} else {
    $stmt = $mysqli->prepare('SELECT title, content, author FROM posts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($title, $content, $author);

    if (!$stmt->fetch()) {
        $stmt->close();
        header('Location: index.php?msg=' . urlencode('Post not found.') . '&type=danger');
        exit;
    }

    $stmt->close();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container mt-4">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="display-6 mb-2">Edit Post</h1>
            <p class="text-muted mb-0">Update the blog post details below.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary needs-loading">Back to Dashboard</a>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" class="secure-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo e($title); ?>" data-validate="title" required minlength="3" maxlength="255">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="6" data-validate="content" required minlength="10"><?php echo e($content); ?></textarea>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                    <label for="author" class="form-label">Author</label>
                    <input type="text" name="author" id="author" class="form-control" value="<?php echo e($author); ?>" data-validate="author" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Update Post</button>
                    <a href="index.php" class="btn btn-secondary needs-loading">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
