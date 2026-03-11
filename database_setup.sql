-- database_setup.sql
-- Run this SQL file in phpMyAdmin or MySQL command line to create your database

-- Create Database
CREATE DATABASE IF NOT EXISTS ecommerce_admin;
USE ecommerce_admin;

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admins (admin_id, username, full_name, email, password) 
VALUES ('ADMIN001', 'admin', 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    product_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO categories (name, slug, description, status, product_count) VALUES
('Electronics', 'electronics', 'Electronic devices and gadgets', 'Active', 15),
('Clothing', 'clothing', 'Fashion and apparel', 'Active', 25),
('Books', 'books', 'Books and literature', 'Inactive', 8);

-- 3. Products/Inventory Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    discount INT DEFAULT 0,
    visibility TINYINT(1) DEFAULT 1,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert sample products
INSERT INTO products (name, price, stock, discount, visibility, category_id) VALUES
('Laptop', 999.00, 50, 10, 1, 1),
('Mouse', 25.00, 5, 0, 1, 1),
('Keyboard', 45.00, 0, 15, 0, 1);

-- 4. Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('Active', 'Suspended', 'Inactive') DEFAULT 'Active',
    verified ENUM('Yes', 'No') DEFAULT 'No',
    join_date DATE NOT NULL,
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample customers
INSERT INTO customers (name, email, phone, address, status, verified, join_date, total_orders, total_spent) VALUES
('Rahim Ahmed', 'rahim@example.com', '+880 1712-345678', 'Dhaka, Bangladesh', 'Active', 'Yes', '2024-01-15', 5, 2500.00),
('Karim Hossain', 'karim@example.com', '+880 1812-987654', 'Chittagong, Bangladesh', 'Active', 'No', '2024-02-20', 2, 750.00),
('Fatima Khan', 'fatima@example.com', '+880 1912-456789', 'Sylhet, Bangladesh', 'Suspended', 'Yes', '2024-03-10', 0, 0.00);

-- 5. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    customer_name VARCHAR(100) NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Shipped', 'Delivered', 'Cancelled', 'Refunded') DEFAULT 'Pending',
    delivery_info VARCHAR(255) DEFAULT 'Not Assigned',
    refund ENUM('Yes', 'No') DEFAULT 'No',
    order_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert sample orders
INSERT INTO orders (customer_id, customer_name, product_id, product_name, quantity, total_price, status, delivery_info, order_date) VALUES
(1, 'Rahim', 1, 'Laptop', 1, 899.10, 'Pending', 'Not Assigned', CURDATE()),
(2, 'Karim', 2, 'Mouse', 2, 50.00, 'Shipped', 'On the way', CURDATE());

-- 6. Homepage Content Table
CREATE TABLE IF NOT EXISTS homepage_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_title VARCHAR(255) NOT NULL,
    hero_subtitle VARCHAR(255) NOT NULL,
    hero_button_text VARCHAR(50) NOT NULL,
    featured_title VARCHAR(255) NOT NULL,
    promo_text TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default homepage content
INSERT INTO homepage_content (hero_title, hero_subtitle, hero_button_text, featured_title, promo_text) VALUES
('Welcome to Our Store', 'Find the best products at amazing prices', 'Shop Now', 'Featured Products', 'Get 20% OFF on all products this week!');

-- 7. Promotional Banners Table
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    link VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample banners
INSERT INTO banners (title, description, link, status) VALUES
('Summer Sale', 'Up to 50% off on selected items', '/sale', 'Active'),
('New Arrivals', 'Check out our latest products', '/new', 'Active');