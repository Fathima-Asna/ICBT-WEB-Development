-- ============================================================
-- GlobeTrek Adventures — MySQL Database Schema
-- Phase 1: Database Creation, Tables, and Seed Data
-- ============================================================
-- Run this script in MySQL Workbench, phpMyAdmin, or CLI:
--   mysql -u root -p < schema.sql
-- ============================================================

-- Create and use the database
CREATE DATABASE IF NOT EXISTS globetrek_db;
USE globetrek_db;

-- ============================================================
-- 1. USERS TABLE
--    Stores all user accounts: Admin, Staff, and Customer.
--    The 'role' column uses ENUM to restrict values.
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff', 'Customer') DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. PACKAGES TABLE
--    Stores the travel packages offered by GlobeTrek Adventures.
--    Prices stored as DECIMAL(10,2) for currency precision.
-- ============================================================
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. BOOKINGS TABLE
--    Tracks customer bookings with foreign keys to users and packages.
--    ON DELETE CASCADE: if a user or package is deleted, their bookings
--    are automatically removed to maintain referential integrity.
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- ============================================================
-- SEED DATA (Dummy Data for Testing)
-- ============================================================

-- Insert Default Accounts
-- NOTE: Passwords are plaintext for academic purposes only.
-- In production, always hash passwords with BCrypt or PBKDF2.
INSERT INTO users (full_name, email, password, role) VALUES
('Super Admin', 'admin@globetrek.com', 'Admin@123', 'Admin'),
('Agency Staff', 'staff@globetrek.com', 'Staff@123', 'Staff'),
('John Doe', 'john.doe@example.com', 'Customer@123', 'Customer'),
('Jane Smith', 'jane.smith@example.com', 'Customer@123', 'Customer');

-- Insert Travel Packages (Matching your HTML frontend categories)
INSERT INTO packages (name, destination, price, description) VALUES
('Swiss Alps Expedition', 'Switzerland', 2500.00, 'Premium skiing and luxury chalet stay in the snowy peaks of the Swiss Alps.'),
('Bali Beach Retreat', 'Indonesia', 1200.00, 'Tropical relaxation in a private beach villa with guided cultural tours.'),
('Kyoto Heritage Tour', 'Japan', 1800.00, 'Immersive historical journey through temples, shrines, and traditional eco pods.');

-- Insert Dummy Bookings for the Admin to Manage
-- user_id 3 = John Doe, user_id 4 = Jane Smith
INSERT INTO bookings (user_id, package_id, status) VALUES
(3, 1, 'Pending'),
(4, 2, 'Confirmed'),
(3, 3, 'Completed'),
(4, 1, 'Pending');
