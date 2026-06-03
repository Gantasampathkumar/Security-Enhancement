<?php
// config/role_check.php
// Compatibility middleware for role-based page protection.

require_once __DIR__ . '/../includes/auth.php';

function hasRole(string $role): bool
{
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
}

function hasAnyRole(array $roles): bool
{
    return isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], $roles, true);
}

function requireRole(string $role): void
{
    if (!hasRole($role)) {
        header('Location: /access_denied.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireRole('admin');
}

function requireEditorOrAdmin(): void
{
    if (!hasAnyRole(['admin', 'editor'])) {
        header('Location: /access_denied.php');
        exit;
    }
}
