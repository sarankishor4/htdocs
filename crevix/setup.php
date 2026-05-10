<?php
require 'db.php';

echo "<h2 style='font-family:sans-serif;'>🔧 Crevix Database Setup</h2>";

// Drop old tables in correct order (foreign keys)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DROP TABLE IF EXISTS comments");
$conn->query("DROP TABLE IF EXISTS likes");
$conn->query("DROP TABLE IF EXISTS media");
$conn->query("DROP TABLE IF EXISTS users");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "<p>Old tables dropped.</p>";

// Users table
$sql = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'assets/img/default_avatar.png',
    bio TEXT,
    role ENUM('user','admin') DEFAULT 'user',
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql)) echo "<p>✅ Table <b>users</b> created.</p>";
else echo "<p>❌ users error: " . $conn->error . "</p>";

// Media table
$sql = "CREATE TABLE media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    type ENUM('video','photo') NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    status ENUM('active','hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql)) echo "<p>✅ Table <b>media</b> created.</p>";
else echo "<p>❌ media error: " . $conn->error . "</p>";

// Comments table
$sql = "CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    media_id INT,
    user_id INT,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql)) echo "<p>✅ Table <b>comments</b> created.</p>";
else echo "<p>❌ comments error: " . $conn->error . "</p>";

// Likes table
$sql = "CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    media_id INT,
    user_id INT,
    UNIQUE KEY unique_like (media_id, user_id),
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql)) echo "<p>✅ Table <b>likes</b> created.</p>";
else echo "<p>❌ likes error: " . $conn->error . "</p>";

// Create default admin account
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@crevix.com', '$admin_pass', 'admin')");
echo "<p>✅ Default admin created: <b>admin / admin123</b></p>";

echo "<hr><h3>🎉 Setup Complete! <a href='home.php'>Go to Crevix →</a></h3>";
?>
