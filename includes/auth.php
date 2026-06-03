<?php
// includes/auth.php
// Authentication, authorization, ownership checks, and audit logging.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/validation.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function getCurrentUser(): array
{
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? 'viewer',
    ];
}

function isAdmin(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

function isEditor(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'editor';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php?msg=' . urlencode('Please login to access this page.'));
        exit;
    }
}

function requireRole(array $roles): void
{
    requireLogin();
    $role = $_SESSION['user_role'] ?? 'viewer';
    if (!in_array($role, $roles, true)) {
        header('Location: access_denied.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireRole(['admin']);
}

function requireEditorOrAdmin(): void
{
    requireRole(['admin', 'editor']);
}

function record_activity(mysqli $mysqli, ?int $user_id, string $action): void
{
    $stmt = $mysqli->prepare('INSERT INTO activity_logs (user_id, action, timestamp) VALUES (?, ?, NOW())');
    if ($stmt) {
        $stmt->bind_param('is', $user_id, $action);
        $stmt->execute();
        $stmt->close();
    }
}

function register_user(mysqli $mysqli, string $username, string $email, string $password, string $role = 'viewer'): array
{
    $errors = [];
    if ($username === '' || mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    }
    $errors = array_merge($errors, validate_email_address($email), validate_password_strength($password), validate_role($role));
    if ($errors) {
        return ['success' => false, 'message' => implode(' ', $errors)];
    }

    $stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (username, email, password, role, failed_attempts, locked_until, created_at) VALUES (?, ?, ?, ?, 0, NULL, NOW())');
    $stmt->bind_param('ssss', $username, $email, $hash, $role);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok
        ? ['success' => true, 'message' => 'Registration successful. Please login.']
        : ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

function attempt_login(mysqli $mysqli, string $usernameOrEmail, string $password): array
{
    $stmt = $mysqli->prepare('SELECT id, username, email, password, role, failed_attempts, locked_until FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }

    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        return ['success' => false, 'message' => 'Account is locked for 15 minutes after too many failed login attempts.'];
    }

    if (password_verify($password, $user['password'])) {
        $stmt = $mysqli->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $stmt->close();

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'] ?: 'viewer';

        record_activity($mysqli, (int)$user['id'], 'User Login');
        return ['success' => true, 'message' => 'Login successful.'];
    }

    $failed = (int)$user['failed_attempts'] + 1;
    $lockedUntil = $failed >= 5 ? date('Y-m-d H:i:s', time() + 15 * 60) : null;
    $stmt = $mysqli->prepare('UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?');
    $stmt->bind_param('isi', $failed, $lockedUntil, $user['id']);
    $stmt->execute();
    $stmt->close();

    return ['success' => false, 'message' => 'Invalid credentials.'];
}

function logout_user(?mysqli $mysqli = null): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId && $mysqli) {
        record_activity($mysqli, (int)$userId, 'User Logout');
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function canEditPost(mysqli $mysqli, int $postId): bool
{
    if (isAdmin()) {
        return true;
    }
    if (!isEditor()) {
        return false;
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);
    $stmt = $mysqli->prepare('SELECT user_id FROM posts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $stmt->bind_result($ownerId);
    $found = $stmt->fetch();
    $stmt->close();

    return $found && (int)$ownerId === $userId;
}
?>
