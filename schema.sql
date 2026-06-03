-- schema.sql
-- Secure database schema and migration helpers for php-crud-blog.

CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
  failed_attempts INT(11) NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  author VARCHAR(100) NOT NULL DEFAULT '',
  user_id INT(11) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_posts_user_id (user_id),
  CONSTRAINT fk_posts_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NULL,
  action VARCHAR(100) NOT NULL,
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_user_id (user_id),
  CONSTRAINT fk_activity_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Run these ALTER statements manually only if your existing tables are missing these columns.
-- ALTER TABLE users MODIFY role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer';
-- ALTER TABLE users ADD failed_attempts INT(11) NOT NULL DEFAULT 0;
-- ALTER TABLE users ADD locked_until DATETIME NULL;
-- ALTER TABLE posts ADD user_id INT(11) NULL;
-- ALTER TABLE posts ADD INDEX idx_posts_user_id (user_id);
-- ALTER TABLE posts ADD CONSTRAINT fk_posts_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- After registering your first account, make yourself admin:
-- UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
