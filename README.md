# Secure PHP CRUD Blog Management System

## Project Overview

This repository contains the security enhancements for a PHP CRUD blog management system. The application was upgraded from a basic blog to a secure management system with authentication, validation, prepared statements, and role-based access control.

## Completed Features

- Secure user registration, login, and logout
- Password hashing (`password_hash`) and verification (`password_verify`)
- Session management with regeneration
- Prepared statements for all database operations (SQL injection protection)
- Server-side and client-side validation with real-time feedback and password strength indicator
- Role-based access control with Admin, Editor, and Viewer roles
- CSRF token protection
- XSS protection through escaped output
- Security headers
- Login attempt protection and account lockout after 5 failed attempts
- Activity logging and audit trail
- User management for admin users
- Access denied page
- Updated database schema and documentation

## User Roles

- **Admin:** Create, edit, delete posts; manage users
- **Editor:** Create posts, edit own posts, view posts
- **Viewer:** View posts only

## Security Measures Implemented

- Prepared statements with MySQLi
- CSRF tokens on forms
- XSS output escaping
- Secure password hashing
- Brute-force login protection
- Role-based page protection
- Secure session handling
- Security headers
- Audit/activity logs

## Database Updates

Updated schema includes:

- `users.role`
- `users.failed_attempts`
- `users.locked_until`
- `posts.user_id`
- `activity_logs` table

## Verification

- All PHP files passed syntax checks
- Database migration completed
- App pages return HTTP 200
- Login page is functional
- Role fields verified in database
- Activity log table verified
- No raw `mysqli_query()` or `->query()` calls found in code

## Project Links

- Local Application: http://localhost/php-crud-blog/login.php
- Dashboard: http://localhost/php-crud-blog/index.php
- Registration Page: http://localhost/php-crud-blog/register.php
- User Management: http://localhost/php-crud-blog/users.php
- Access Denied Page: http://localhost/php-crud-blog/access_denied.php
- phpMyAdmin: http://localhost/phpmyadmin
- Project Folder: `C:\xampp\htdocs\php-crud-blog`

## Notes

This repository now contains the security configuration and validation helper files needed to support the secure blog management system.
