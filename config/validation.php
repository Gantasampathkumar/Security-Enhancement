<?php
// config/validation.php
// Reusable server-side validation helpers for forms.

function validate_title(string $title): array
{
    $errors = [];
    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
        $errors[] = 'Title must be between 3 and 255 characters.';
    }
    return $errors;
}

function validate_content(string $content): array
{
    $errors = [];
    if ($content === '') {
        $errors[] = 'Content is required.';
    } elseif (mb_strlen($content) < 10) {
        $errors[] = 'Content must be at least 10 characters.';
    }
    return $errors;
}

function validate_author(string $author): array
{
    $errors = [];
    if ($author === '') {
        $errors[] = 'Author is required.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $author)) {
        $errors[] = 'Author must contain only letters and spaces.';
    }
    return $errors;
}

function validate_email_address(string $email): array
{
    if ($email === '') {
        return ['Email is required.'];
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? [] : ['Please enter a valid email address.'];
}

function validate_password_strength(string $password, bool $required = true): array
{
    $errors = [];
    if ($password === '') {
        return $required ? ['Password is required.'] : [];
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must include an uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must include a lowercase letter.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Password must include a number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must include a special character.';
    }
    return $errors;
}

function validate_role(string $role): array
{
    return in_array($role, ['admin', 'editor', 'viewer'], true) ? [] : ['Please choose a valid role.'];
}

function validate_post_form(string $title, string $content, string $author): array
{
    return array_merge(validate_title($title), validate_content($content), validate_author($author));
}
?>
