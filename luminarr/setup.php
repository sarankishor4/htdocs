<?php
require_once 'api/config.php';

try {
    // Connect without database name first to create it
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    // Tables
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            role ENUM('user', 'admin') DEFAULT 'user',
            avatar VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            image VARCHAR(255),
            parent_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            sale_price DECIMAL(10, 2) DEFAULT NULL,
            category_id INT,
            image VARCHAR(255),
            stock INT DEFAULT 0,
            featured BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            shipping_address TEXT,
            billing_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            size VARCHAR(10),
            color VARCHAR(20),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            product_id INT,
            quantity INT DEFAULT 1,
            size VARCHAR(10),
            color VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )"
    ];

    foreach ($queries as $query) {
        $pdo->exec($query);
    }

    // Insert Default Admin if not exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@luminarr.com']);
    if (!$stmt->fetch()) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
            ->execute(['Admin', 'admin@luminarr.com', $hashedPassword, 'admin']);
    }

    echo "Database and tables created successfully!";
} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}
?>
