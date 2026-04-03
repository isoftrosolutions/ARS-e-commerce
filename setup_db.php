<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ars_ecommerce");
    $pdo->exec("USE ars_ecommerce");

    // Drop old tables if they exist to start fresh
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS order_items, orders, products, categories, users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Users Table
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE,
        mobile VARCHAR(15) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        address TEXT,
        role ENUM('admin', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Categories Table
    $pdo->exec("CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL
    )");

    // Products Table
    $pdo->exec("CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        discount_price DECIMAL(10, 2),
        category_id INT,
        stock INT DEFAULT 0,
        image VARCHAR(255),
        sku VARCHAR(50) UNIQUE,
        is_featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");

    // Orders Table
    $pdo->exec("CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('COD', 'eSewa', 'BankQR') NOT NULL,
        payment_status ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
        delivery_status ENUM('Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
        transaction_id VARCHAR(100),
        payment_proof VARCHAR(255),
        address TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Order Items Table
    $pdo->exec("CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    // Seed Data
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (full_name, email, mobile, password, role) VALUES ('Admin User', 'admin@ars.com', '9800000000', '$admin_pass', 'admin')");

    $pdo->exec("INSERT INTO categories (name, slug) VALUES ('Electronics', 'electronics'), ('Clothing', 'clothing'), ('Home & Living', 'home-living')");

    echo "Database and tables created successfully with seed data.\n";
    echo "Admin Account: admin@ars.com | Pass: admin123\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
