<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$username = '';
$email = '';
$role = 'viewer';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    if (!$errors) {
        $result = register_user($mysqli, $username, $email, $password, $role);
        if ($result['success']) {
            record_activity($mysqli, (int)$_SESSION['user_id'], 'Create User');
            header('Location: users.php?msg=' . urlencode('User created successfully.') . '&type=success');
            exit;
        }
        $errors[] = $result['message'];
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-0">Create User</h1>
            <p class="text-muted">Add a new user account with a secure role.</p>
        </div>
        <a href="users.php" class="btn btn-outline-secondary">Back to Users</a>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" class="secure-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" name="username" id="username" class="form-control" value="<?php echo e($username); ?>" required minlength="3" maxlength="50"><div class="invalid-feedback"></div></div>
                <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" name="email" id="email" class="form-control" value="<?php echo e($email); ?>" data-validate="email" required><div class="invalid-feedback"></div></div>
                <div class="mb-3"><label for="password" class="form-label">Password</label><input type="password" name="password" id="password" class="form-control" data-validate="password" required><div class="password-strength mt-2"><div class="password-strength-bar"></div></div><small class="password-strength-text text-muted">Use uppercase, lowercase, number, and special character.</small><div class="invalid-feedback"></div></div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <?php foreach (['admin', 'editor', 'viewer'] as $option): ?>
                            <option value="<?php echo e($option); ?>" <?php echo $role === $option ? 'selected' : ''; ?>><?php echo e(ucfirst($option)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
