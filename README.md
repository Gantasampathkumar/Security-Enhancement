# PHP CRUD Blog Management System

A secure Core PHP Blog Management System built with MySQL, Bootstrap 5, HTML5, CSS3, JavaScript, and XAMPP. CRUD, search, and pagination are preserved, and this phase adds authentication, validation, RBAC, CSRF protection, XSS protection, security headers, login-attempt protection, and audit logging.

## Features

- Create, read, update, and delete blog posts
- Search by title, content, or author
- Pagination and sorting
- Secure registration, login, logout, and session handling
- Role-based access control: Admin, Editor, Viewer
- Prepared statements for all database operations
- Server-side validation for login, registration, post, user, and search forms
- JavaScript real-time validation with Bootstrap styles
- Password strength indicator
- CSRF tokens on protected forms
- Escaped output using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
- Security headers from `config/security.php`
- Account lockout after 5 failed login attempts for 15 minutes
- Activity logging for login, logout, post changes, and user changes

## Roles

| Role | Permissions |
| --- | --- |
| Admin | Create posts, edit any post, delete posts, manage users |
| Editor | Create posts, edit own posts, view posts |
| Viewer | View posts only |

New registrations are created as `viewer` accounts by default. An admin can change roles from User Management.

## Project Structure

```text
php-crud-blog/
├── index.php
├── create.php
├── edit.php
├── delete.php
├── login.php
├── register.php
├── logout.php
├── dashboard.php
├── access_denied.php
├── users.php
├── user_create.php
├── user_edit.php
├── user_delete.php
├── user_management.php
├── config/
│   ├── database.php
│   ├── security.php
│   ├── auth.php
│   ├── role_check.php
│   └── validation.php
├── includes/
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── pages/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── access_denied.php
│   └── user_management.php
├── css/style.css
├── js/script.js
├── schema.sql
└── README.md
```

## Database Setup

Open phpMyAdmin and select your database, usually `blog`. Import `schema.sql` for a fresh setup.

For an existing database, run only the needed migration statements from `schema.sql`:

```sql
ALTER TABLE users MODIFY role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer';
ALTER TABLE users ADD failed_attempts INT(11) NOT NULL DEFAULT 0;
ALTER TABLE users ADD locked_until DATETIME NULL;
ALTER TABLE posts ADD user_id INT(11) NULL;
ALTER TABLE posts ADD INDEX idx_posts_user_id (user_id);
ALTER TABLE posts ADD CONSTRAINT fk_posts_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
```

Create activity logs if missing:

```sql
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NULL,
  action VARCHAR(100) NOT NULL,
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

After registering your first account, make yourself admin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

## Configuration

Edit `config/database.php` if your database name or credentials differ:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "blog";
```

## How to Run in XAMPP

1. Put the folder at `C:\xampp\htdocs\php-crud-blog`.
2. Start Apache and MySQL in XAMPP Control Panel.
3. Import or migrate the database using `schema.sql`.
4. Visit `http://localhost/php-crud-blog/register.php`.
5. Register your first user.
6. In phpMyAdmin, run the admin update query above.
7. Login at `http://localhost/php-crud-blog/login.php`.

## Validation Rules

Post forms:

- Title: required, 3 to 255 characters
- Content: required, minimum 10 characters
- Author: required, letters and spaces only

User forms:

- Email: required, valid email format
- Password: minimum 8 characters, uppercase, lowercase, number, and special character
- Role: admin, editor, or viewer only

Validation runs on both the server and in JavaScript.

## Security Measures Implemented

- SQL injection protection through prepared statements and `bind_param()`
- XSS protection through escaped output helper `e()`
- CSRF protection through session tokens on forms
- Password hashing with `password_hash()`
- Password verification with `password_verify()`
- Session ID regeneration after login
- HTTP-only and strict-mode session settings
- Security headers including X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, and Content-Security-Policy
- Login brute-force protection with `failed_attempts` and `locked_until`
- Role-based redirects to `access_denied.php`
- Activity logs in `activity_logs`

## How to Test Authentication

1. Register a new account.
2. Try logging in with a wrong password 5 times.
3. Confirm the account locks for 15 minutes.
4. Login with the correct password after unlocking or reset `locked_until` in phpMyAdmin.
5. Confirm logout returns you to the login page.

## How to Test Roles

1. Create three users and assign roles in User Management or phpMyAdmin.
2. Login as Viewer: confirm you can view posts only.
3. Login as Editor: confirm you can create posts and edit only posts you created.
4. Login as Admin: confirm you can edit/delete posts and manage users.
5. Try visiting restricted URLs directly and confirm `access_denied.php` appears.

## How to Verify SQL Injection Protection

Try these values in search, login, title, or author fields:

```text
' OR '1'='1
admin' --
1; DROP TABLE posts;
```

The application should not bypass login, leak data, or execute destructive SQL because queries use prepared statements.

## How to Verify XSS Protection

Create or search for content like:

```html
<script>alert('xss')</script>
```

It should display as text instead of running JavaScript.

## Screenshots

Add screenshots here after running the application:

- Login page
- Registration page
- Dashboard
- User Management
- Access Denied page
