-- Reference schema if you switch config.php to use MySQL instead of SQLite.
CREATE DATABASE IF NOT EXISTS taskmanager CHARACTER SET utf8mb4;
USE taskmanager;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    due_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tasks_user (user_id),
    INDEX idx_tasks_status (status)
) ENGINE=InnoDB;
