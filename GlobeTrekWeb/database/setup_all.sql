-- ============================================================
-- GlobeTrek Adventures — Master Setup Script
-- ============================================================
-- This script executes all database files in their required order
-- of dependencies.
-- ============================================================

-- 1. Initialize Database
SOURCE 01_db_init.sql;

-- 2. Create Users Table & Seeds
SOURCE 02_table_users.sql;

-- 3. Create Packages Table & Seeds
SOURCE 03_table_packages.sql;

-- 4. Create Bookings Table & Seeds
SOURCE 04_table_bookings.sql;

SELECT 'GlobeTrek database set up successfully!' AS Status;
