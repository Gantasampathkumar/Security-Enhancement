<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$usernameOrEmail = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }
    if ($usernameOrEmail === '') {
        $errors[] = 'Username or email is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        $result = attempt_login($mysqli, $usernameOrEmail, $password);
        if ($result['success']) {
            header('Location: index.php?msg=' . urlencode('Login successful.') . '&type=success');
            exit;
        }
        $errors[] = $result['message'];
    }
}

$alertMessage = isset($_GET['msg']) ? trim($_GET['msg']) : '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="mb-0">Secure Login</h1>
                    <p class="text-muted">Sign in to access your blog dashboard.</p>
                </div>
                <a href="register.php" class="btn btn-outline-secondary">Register</a>
            </div>

            <?php if ($alertMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e($alertMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

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
                        <div class="mb-3">
                            <label for="username_or_email" class="form-label">Username or Email</label>
                            <input type="text" name="username_or_email" id="username_or_email" class="form-control" value="<?php echo e($usernameOrEmail); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
