# 🗄 GlobeTrek Adventures — Database Scripts Directory

This directory contains modularized SQL files for the database layout. Each file is dedicated to a specific table entity, including its definition and seed data.

## 📁 File Structure

- [01_db_init.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/01_db_init.sql): Creates and switches focus to the `globetrek_db` database.
- [02_table_users.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/02_table_users.sql): Sets up the user profile/auth table and inserts default credentials.
- [03_table_packages.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/03_table_packages.sql): Establishes the travel catalog packages table and inserts default tours.
- [04_table_bookings.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/04_table_bookings.sql): Declares the booking relationship table with proper foreign keys and seed orders.
- [setup_all.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/setup_all.sql): A master SQL script using the `SOURCE` command to run all modular files in correct sequential order.
- [schema.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/schema.sql): A single-file combined compilation for simple direct execution.

## 🚀 How to Initialize

You can set up the database using the master script or by importing the consolidated `schema.sql` file.

### Option A: Using the Master Script (MySQL CLI)
Open your terminal inside the `database` directory and run:
```bash
mysql -u root -p < setup_all.sql
```

### Option B: Running files in sequence manually
Import the scripts in the following order:
1. `01_db_init.sql`
2. `02_table_users.sql`
3. `03_table_packages.sql`
4. `04_table_bookings.sql`
