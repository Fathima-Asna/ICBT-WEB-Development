-- ============================================================
-- GlobeTrek Adventures — Users Table & Seed Data
-- ============================================================

USE globetrek_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff', 'Customer') DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data (Academic/Testing accounts)
-- Passwords are in plaintext for scholastic testing.
INSERT INTO users (full_name, email, password, role) VALUES
('Super Admin', 'admin@globetrek.com', 'Admin@123', 'Admin'),
('Agency Staff', 'staff@globetrek.com', 'Staff@123', 'Staff'),
('John Doe', 'john.doe@example.com', 'Customer@123', 'Customer'),
('Jane Smith', 'jane.smith@example.com', 'Customer@123', 'Customer')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);
