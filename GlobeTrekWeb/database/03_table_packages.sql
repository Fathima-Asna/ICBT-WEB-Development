-- ============================================================
-- GlobeTrek Adventures — Travel Packages Table & Seed Data
-- ============================================================

USE globetrek_db;

CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data (Default curated tours)
INSERT INTO packages (name, destination, price, description) VALUES
('Swiss Alps Expedition', 'Switzerland', 2500.00, 'Premium skiing and luxury chalet stay in the snowy peaks of the Swiss Alps.'),
('Bali Beach Retreat', 'Indonesia', 1200.00, 'Tropical relaxation in a private beach villa with guided cultural tours.'),
('Kyoto Heritage Tour', 'Japan', 1800.00, 'Immersive historical journey through temples, shrines, and traditional eco pods.');
