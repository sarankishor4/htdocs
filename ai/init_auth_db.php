<?php
require_once 'core/Config.php';
require_once 'core/Database.php';

$db = \AI\Core\Database::getInstance();

echo "Initializing Authentication Tables...\n";

// Users Table
$db->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    reset_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

echo "Tables Initialized Successfully.\n";
?>
