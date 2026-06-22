-- GlobeTrek Adventures Database Schema

CREATE DATABASE IF NOT EXISTS globetrek_db;
USE globetrek_db;

-- Drop tables in reverse dependency order to avoid foreign key failures
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS queries;
DROP TABLE IF EXISTS saved_packages;
DROP TABLE IF EXISTS packages;
DROP TABLE IF EXISTS users;

-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL CHECK (role IN ('customer', 'staff', 'admin')),
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- 2. Tour Packages Table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    likes_count INT DEFAULT 0,
    image_url VARCHAR(255)
);

-- 3. Saved Packages Table (Many-to-Many Relationship)
CREATE TABLE saved_packages (
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, package_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- 4. Queries Table
CREATE TABLE queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    question_text TEXT NOT NULL,
    answer_text TEXT DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Answered')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- 5. Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'Confirmed' CHECK (status IN ('Pending', 'Confirmed', 'Cancelled')),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- Insert Initial Users (Plaintext for development/testing)
INSERT INTO users (role, username, password) VALUES
('admin', 'admin_globetrek', 'admin123'),
('staff', 'staff_negombo', 'staff123'),
('customer', 'traveler_srilanka', 'traveler123');

-- Insert Premium Tour Packages in Negombo & Sri Lanka
INSERT INTO packages (id, destination, price, description, likes_count, image_url) VALUES
(1, 'Negombo Lagoon & Canal Boat Safari', 75.00, 'Explore ancient Dutch canals, lush mangrove forests, and fishing villages.', 42, 'images/negombo_lagoon.jpg'),
(2, 'Sigiriya Rock Fortress & Dambulla Caves', 120.00, 'Historical journey to the sky palace and cave temples.', 128, 'images/sigiriya.jpg'),
(3, 'Ella Panoramic Train Tour', 95.00, 'Scenic mountain rail journey and the Nine Arch Bridge.', 95, 'images/ella.jpg');
