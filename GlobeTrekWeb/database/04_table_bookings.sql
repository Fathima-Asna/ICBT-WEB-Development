-- ============================================================
-- GlobeTrek Adventures — Bookings Table & Seed Data
-- ============================================================

USE globetrek_db;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- Seed Data for Testing (John Doe and Jane Smith bookings)
INSERT INTO bookings (user_id, package_id, status) VALUES
(3, 1, 'Pending'),
(4, 2, 'Confirmed'),
(3, 3, 'Completed'),
(4, 1, 'Pending');
