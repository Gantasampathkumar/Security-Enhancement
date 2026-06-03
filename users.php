<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$stmt = $mysqli->prepare('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
        <div>
            <h1 class="mb-2">User Management</h1>
            <p class="text-muted">Admin-only area for user roles and permissions.</p>
        </div>
        <div>
            <a href="user_create.php" class="btn btn-primary me-2 mb-2">Add New User</a>
            <a href="index.php" class="btn btn-secondary mb-2">Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
        <div class="alert alert-<?php echo e(($_GET['type'] ?? '') === 'danger' ? 'danger' : 'success'); ?> alert-dismissible fade show" role="alert">
            <?php echo e($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo (int)$row['id']; ?></td>
                                    <td><?php echo e($row['username']); ?></td>
                                    <td><?php echo e($row['email']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo e(ucfirst($row['role'])); ?></span></td>
                                    <td><?php echo e($row['created_at']); ?></td>
                                    <td>
                                        <a href="user_edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                        <?php if ((int)$row['id'] !== (int)$_SESSION['user_id']): ?>
                                            <form method="post" action="user_delete.php" class="d-inline delete-form">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No registered users found yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php $stmt->close(); include __DIR__ . '/includes/footer.php'; ?>
