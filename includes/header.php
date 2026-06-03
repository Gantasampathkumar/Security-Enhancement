<?php
require_once __DIR__ . '/../config/security.php';
$loggedIn = isset($_SESSION['user_id']);
$userName = e($_SESSION['user_name'] ?? '');
$userRole = $_SESSION['user_role'] ?? 'viewer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP CRUD Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="loadingOverlay" class="loading-overlay d-none">
        <div class="loading-spinner shadow-lg rounded-4 p-4 bg-white text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3 fw-semibold">Loading, please wait...</div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm navbar-gradient">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <span class="brand-icon">B</span>
                <span>PHP CRUD Blog</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php if ($loggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                        <?php if (in_array($userRole, ['admin', 'editor'], true)): ?>
                            <li class="nav-item"><a class="nav-link" href="create.php">Add Post</a></li>
                        <?php endif; ?>
                        <?php if ($userRole === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <button id="themeToggle" class="btn btn-outline-light btn-sm me-2" type="button" data-bs-toggle="tooltip" title="Toggle dark mode">
                                <i class="bi bi-moon-stars"></i>
                            </button>
                        </li>
                        <li class="nav-item d-lg-flex align-items-center">
                            <span class="nav-link text-white-50">Welcome, <?php echo $userName; ?> <span class="badge bg-light text-dark ms-1"><?php echo e(ucfirst($userRole)); ?></span></span>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
