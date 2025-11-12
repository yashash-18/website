CREATE DATABASE IF NOT EXISTS lamp_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lamp_app;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  profile_pic VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (username, email, password_hash, role)
VALUES ('admin', 'admin@example.com', '$2y$10$u5q3K5i7wN7mY5j0B1QxLeGQk1BucK6iYz7o3qIYk6pQ1aGu1E3aW', 'admin')
ON DUPLICATE KEY UPDATE username=username;

