-- GlobeTrek Adventures Database Schema
-- Compatible with TiDB Serverless & MySQL 8.0+

CREATE DATABASE IF NOT EXISTS `globetrek_db`;
USE `globetrek_db`;

-- Drop tables if they exist to allow clean re-runs
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `blog_posts`;
DROP TABLE IF EXISTS `accommodations`;
DROP TABLE IF EXISTS `packages`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Packages Table
CREATE TABLE `packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `destination` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `itinerary` TEXT NOT NULL, -- Semi-colon or new-line separated itinerary points
  `image_url` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Accommodations Table
CREATE TABLE `accommodations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL, -- e.g., Hotel, Resort, Villa
  `location` VARCHAR(100) NOT NULL,
  `price_per_night` DECIMAL(10, 2) NOT NULL,
  `rating` DECIMAL(2, 1) DEFAULT 4.0,
  `description` TEXT NOT NULL,
  `image_url` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bookings Table
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `accommodation_id` INT DEFAULT NULL,
  `travel_date` DATE NOT NULL,
  `status` ENUM('pending', 'approved', 'cancelled') DEFAULT 'pending',
  `total_price` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_accommodation` FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Reviews Table
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `review_text` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Blog Posts Table
CREATE TABLE `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `content` TEXT NOT NULL,
  `author` VARCHAR(100) NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- SEED DATA
-- ==========================================

-- Default Users (Password for both is: password)
-- Hash generated using password_hash('password', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`) VALUES
('admin', 'admin@globetrek.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('traveler', 'traveler@globetrek.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Featured Packages
INSERT INTO `packages` (`title`, `destination`, `price`, `duration`, `description`, `itinerary`, `image_url`) VALUES
('Amalfi Coast Escape', 'Italy', 1499.00, '7 Days, 6 Nights', 'Explore the vertical landscapes, cliffside gardens, and azure waters of the legendary Amalfi Coast. Visit Sorrento, Positano, and Ravello, enjoying guided historical walks, coastal yacht excursions, and hands-on cooking classes with native Italian chefs.', 'Day 1: Arrival in Naples & Transfer to Sorrento - Welcome dinner;Day 2: Walking Tour of Historic Sorrento & Limoncello Tasting;Day 3: Amalfi Coastal Cruise & Positano Exploration;Day 4: Guided Tour of Ravello Gardens and Villa Rufolo;Day 5: Excursion to Pompeii & Mount Vesuvius Vineyard lunch;Day 6: Capri Yacht Tour & Blue Grotto visit;Day 7: Departure transfer to Naples Airport', 'https://images.unsplash.com/photo-1533900298318-6b8da08a523e?auto=format&fit=crop&w=800&q=80'),
('Kyoto Heritage & Zen', 'Japan', 1899.00, '6 Days, 5 Nights', 'Immerse yourself in Japan\'s cultural heart. Wander through towering bamboo groves, participate in an authentic tea ceremony, explore historical temples, and experience the neon energy of nearby Osaka.', 'Day 1: Arrival in Kyoto - Traditional kaiseki welcome dinner;Day 2: Fushimi Inari Shrine & Kiyomizu-dera Temple hike;Day 3: Arashiyama Bamboo Grove & Kinkaku-ji (Golden Pavilion);Day 4: Traditional Matcha Tea Ceremony & Gisha district evening tour;Day 5: Osaka Day Trip - Dotonbori food tour & Osaka Castle;Day 6: Zen meditation morning & Departure transfer', 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=800&q=80'),
('Santorini Sunset Odyssey', 'Greece', 1299.00, '5 Days, 4 Nights', 'Indulge in a premium Greek Island retreat. Admire whitewashed houses overlooking the volcanic caldera, dive into hot springs, and witness the world-famous sunsets of Oia.', 'Day 1: Santorini Arrival - Caldera view cocktail reception;Day 2: Fira to Oia Ridge Hike & sunset viewing;Day 3: Catamaran Sailing Cruise with snorkeling and hot springs;Day 4: Volcanic Vineyard Wine Tasting Tour & Akrotiri Ruins;Day 5: Leisure morning at Kamari black sand beach & Departure', 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?auto=format&fit=crop&w=800&q=80'),
('Swiss Alps Adventure', 'Switzerland', 2199.00, '8 Days, 7 Nights', 'An exhilarating alpine escape in Zermatt and Interlaken. Experience majestic mountain train rides, glacier hiking, paragliding over Swiss lakes, and cozy fondue evenings.', 'Day 1: Arrival in Zurich & Scenic train to Interlaken;Day 2: Paragliding over Lake Thun & Harder Kulm sunset;Day 3: Jungfraujoch - "Top of Europe" high-alpine glacier excursion;Day 4: Travel to Zermatt & Matterhorn viewpoint hike;Day 5: Glacier Paradise cable car & ice palace tour;Day 6: Alpine Lake Hiking (Five Lakes Trail);Day 7: Cheese & Chocolate tasting tour - Fondue dinner;Day 8: Scenic train return to Zurich & departure', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80');

-- Accommodations
INSERT INTO `accommodations` (`name`, `type`, `location`, `price_per_night`, `rating`, `description`, `image_url`) VALUES
('Villa Marina Capri Hotel', 'Resort', 'Italy', 320.00, 4.9, 'Overlooking the Naples Gulf, this premium resort features private gardens, a cliffside swimming pool, and an award-winning spa.', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'),
('Ryokan Kurashiki', 'Villa', 'Japan', 260.00, 4.8, 'Experience luxury traditional lodging. Authentic tatami rooms, private rock-garden views, and multi-course seasonal dining.', 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=800&q=80'),
('Astra Suites Santorini', 'Hotel', 'Greece', 290.00, 4.7, 'Perched on the high cliffs of Imerovigli, offering panoramic sea views, an infinity pool, and customized cave-style architecture.', 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80'),
('The Chedi Andermatt', 'Resort', 'Switzerland', 450.00, 4.9, 'Where alpine chic meets Asian elegance. Five-star service, dynamic indoor/outdoor pools, and Michelin-star dining rooms.', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80');

-- Initial Reviews
INSERT INTO `reviews` (`user_id`, `package_id`, `rating`, `review_text`) VALUES
(2, 1, 5, 'The Amalfi Coast escape was absolutely breathtaking! The yacht tour of Capri and the cooking classes were unforgettable highlights.'),
(2, 3, 5, 'A truly magical experience. Watching the sunset in Oia from the catamaran was a dream come true. Highly recommended!'),
(2, 2, 4, 'Very well-organized cultural trip. The temples in Kyoto are gorgeous, though the hike up Fushimi Inari is quite demanding.');

-- Travel Tips / Blog Posts
INSERT INTO `blog_posts` (`title`, `content`, `author`, `image_url`) VALUES
('Mastering the Art of Light Packing', 'Packing light is the ultimate secret to stress-free travel. Start by choosing a versatile, neutral color scheme so every piece of clothing can be mixed and matched. Always roll your clothes instead of folding them—not only does this compress space, but it also minimizes wrinkles. Restrict yourself to two pairs of shoes max: one for walking and one for dressing up. Lastly, utilize travel compression cubes to compartmentalize your suitcase like a pro. Your back will thank you!', 'Sarah Jenkins', 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80'),
('Top 5 Hidden Gems in Kyoto, Japan', 'While Kinkaku-ji and Fushimi Inari are gorgeous, Kyoto is full of quieter sanctuaries that offer a deeper Zen experience. Visit Gio-ji, a tiny moss-covered temple in Arashiyama that feels like a fairy tale. Head to Otagi Nenbutsu-ji to see 1,200 whimsical stone statues, each with a unique facial expression. For a peaceful afternoon walk away from crowds, seek out the philosopher\'s path in late afternoon, and end your day tasting local sake in the historic Fushimi district.', 'Takeshi Sato', 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=800&q=80'),
('How to Avoid Travel Scams in Europe', 'Traveling through Europe is generally safe, but being aware of common scams keeps your vacation stress-free. Always stay vigilant in crowded transit hubs where pickpockets operate. Watch out for the "friendship bracelet" scam, where vendors try to tie string to your wrist and demand payment. Never sign petitions or buy roses from strangers on the street. Always use official taxi stands or ridesharing apps rather than accepting rides from unmarked drivers inside train stations or airports.', 'Marcus Vance', 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=800&q=80');
