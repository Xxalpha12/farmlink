-- Farm Link Database Schema
-- Based on Chapter 4 System Design: users (farmers/buyers/admin), products, orders

CREATE DATABASE IF NOT EXISTS farmlink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE farmlink;

-- ============================================================
-- USERS TABLE
-- Holds farmers, buyers and the administrator
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('farmer', 'buyer', 'admin') NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) DEFAULT NULL,        -- used by farmers, per Input Design 4.2.1
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- PRODUCTS TABLE
-- Farm produce listings created by farmers
-- Fields mirror the "Add Product" input design (Fig. 4.1):
-- company name, phone number, category, product, price, description
-- ============================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    category VARCHAR(100) NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(30) NOT NULL DEFAULT 'unit',
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ORDERS TABLE
-- Orders placed by buyers against a farmer's product
-- Statuses mirror Output Design 4.2.2: Pending / Delivered
-- ============================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    product_id INT NOT NULL,
    farmer_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    delivery_address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- NOTE: The admin account is NOT seeded here. Run install.php once in your
-- browser after importing this schema — it creates the admin login with a
-- properly generated PHP password_hash() value, then deletes itself.

-- ============================================================
-- Sample categories reference (used in dropdowns)
-- ============================================================
-- Grains, Tubers, Vegetables, Fruits, Livestock, Poultry, Dairy, Other
