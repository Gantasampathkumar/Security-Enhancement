<?php require_once __DIR__ . '/includes/auth.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 640px;">
        <div class="card-body text-center p-5">
            <i class="bi bi-shield-lock display-4 text-danger"></i>
            <h1 class="mt-3">Access Denied</h1>
            <p class="text-muted">Your current role does not have permission to access this page or perform this action.</p>
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
