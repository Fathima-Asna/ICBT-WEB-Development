# GlobeTrek Adventures - PHP Web Application

GlobeTrek Adventures is a premium travel agency web application built with a secure PHP backend and a responsive vanilla frontend. It supports dynamic tour catalog navigation, booking operations, relational inquiry tracking, and role-based client administration.

---

## 🗺️ Site Map & Directory Structure

The page flow is divided into clear navigation structures and AJAX API endpoints:

```mermaid
graph TD
    %% Main pages
    Guest[Guest Visitor] --> Home[index.php - Featured Home]
    Home --> Packages[packages.php - Search Catalog]
    Home --> Contact[contact.php - FAQ Inquiries]
    Home --> Login[login.php - Role-based Sign In]

    %% User Roles routing
    Login --> AuthCustomer{Customer Session?}
    Login --> AuthStaff{Staff Session?}
    Login --> AuthAdmin{Admin Session?}

    AuthCustomer -- Yes --> Dashboard[dashboard.php - Traveler Space]
    AuthStaff -- Yes --> StaffSpace[staff.php - Administration Space]
    AuthAdmin -- Yes --> AdminSpace[admin.php - System Analytics]

    %% Dashboards actions
    Dashboard --> Bookings[My Bookings]
    Dashboard --> Bookmarks[My Bookmarks]
    Dashboard --> MyQuestions[Question Logs]

    StaffSpace --> ReplyQueries[Resolve Q&A Queries]
    StaffSpace --> EditPackages[Update Package pricing/details]
    StaffSpace --> BookingStatus[Confirm/Cancel Bookings]

    AdminSpace --> StatWidgets[Analytics Counters]
    AdminSpace --> StaffManage[Create/Delete Staff Accounts]
    AdminSpace --> PackageManage[Create/Delete Package Catalog]
```

### File Hierarchy
```plaintext
html_asna/
│
├── config/
│   └── db.php                  # Central PDO Database connection script
│
├── api/                        # Asynchronous AJAX endpoints returning JSON
│   ├── login.php               # Processes auth and establishes PHP session
│   ├── toggle-like.php         # Increments package likes dynamically
│   ├── toggle-save.php         # Toggles package bookmarks (Saved Packages)
│   ├── book-package.php        # Logs a new customer booking
│   ├── submit-query.php        # Submits customer questions or staff responses
│   ├── update-booking-status.php# Staff-controlled booking status updates
│   ├── update-package.php      # Staff-controlled package content modifications
│   ├── add-staff.php           # Admin-controlled staff registration
│   ├── delete-staff.php        # Admin-controlled staff removal
│   ├── add-package.php         # Admin-controlled package creation
│   └── delete-package.php      # Admin-controlled package deletion
│
├── css/
│   └── style.css               # Global stylesheets, custom HSL color palette, and micro-animations
│
├── js/
│   └── app.js                  # AJAX fetch requests, button active loading states, and toast triggers
│
├── images/                     # Holds tour packages photography assets
│
├── screenshots/                # Application page layout captures for assignment documentation
│
├── index.php                   # Customer Homepage (Featured catalog introduction)
├── packages.php                # Dedicated search and packages browsing page
├── contact.php                 # Dedicated contact details and Q&A inquiry forms
├── login.php                   # Secure credentials portal with role selection
├── logout.php                  # Destroys session variables and redirects
├── dashboard.php               # Customer Dashboard (My bookings, bookmarks, and queries status)
├── staff.php                   # Travel Agency Staff Dashboard
├── admin.php                   # Administrator Dashboard
└── schema.sql                  # MySQL Relational Database creation and seeds script
```

---

## 🎭 UML Use Case Diagram

The system supports three user actors (`Traveler/Customer`, `Agency Staff`, `Administrator`) plus the generic `Guest` visitor.

```mermaid
flowchart LR
    %% Actors
    subgraph Actors [System User Roles]
        Guest["Guest User"]
        Customer["Traveler (Customer)"]
        Staff["Agency Staff"]
        Admin["System Administrator"]
    end

    %% Use Cases
    subgraph System [GlobeTrek Adventures System]
        UC_Browse("Browse featured/full tours catalog")
        UC_Like("Like tour packages (Instant Counter)")
        UC_Bookmark("Save/Bookmark packages for later")
        UC_Book("Book travel package (Status: Confirmed)")
        UC_Ask("Submit specific package queries")
        UC_Auth("Role-based Sign In/Sign Out")
        UC_Reply("Reply to customer queries")
        UC_Status("Update booking statuses (Pending/Confirmed/Cancelled)")
        UC_EditPkg("Edit package pricing and descriptions")
        UC_StaffManage("Manage Staff Credentials (CRUD)")
        UC_PkgCatalog("Manage Tour Catalog (Add/Delete Packages)")
        UC_Stats("View analytical stats and popularity reports")
    end

    %% Relationships
    Guest --> UC_Browse
    Guest --> UC_Auth

    Customer --> UC_Browse
    Customer --> UC_Like
    Customer --> UC_Bookmark
    Customer --> UC_Book
    Customer --> UC_Ask
    Customer --> UC_Auth

    Staff --> UC_Auth
    Staff --> UC_Reply
    Staff --> UC_Status
    Staff --> UC_EditPkg

    Admin --> UC_Auth
    Admin --> UC_StaffManage
    Admin --> UC_PkgCatalog
    Admin --> UC_Stats
    Admin --> UC_Status
    Admin --> UC_EditPkg
```

---

## 🔄 System Sequence Diagrams

### 1. User Authentication Sequence
```mermaid
sequenceDiagram
    autonumber
    actor User
    participant Login as login.php
    participant API as api/login.php
    participant DB as config/db.php (MySQL)
    
    User->>Login: Select Role, Enter Username & Password, click Sign In
    Login->>API: POST request (JSON payload)
    API->>DB: Query user record matching role and username
    DB-->>API: Return user object (id, password, role)
    alt Credentials Match
        API->>API: session_start() & save session variables
        API-->>Login: JSON response { success: true, redirect: dashboard.php }
        Login-->>User: Redirection to matching workspace
    else Credentials Mismatch
        API-->>Login: JSON response { success: false, message: "Invalid credentials" }
        Login-->>User: Display slide-in Toast Alert warning
    end
```

### 2. Package Booking Flowchart Sequence
```mermaid
sequenceDiagram
    autonumber
    actor Traveler
    participant App as packages.php (JS)
    participant API as api/book-package.php
    participant DB as config/db.php (MySQL)
    participant Dashboard as dashboard.php
    
    Traveler->>App: Click "Book Now" CTA on package card
    alt User Is Guest (No active session)
        App-->>Traveler: Render warning toast: "Please log in to book this package."
    else User Is Logged In
        App->>App: Change button state to "Booking..." (disabled)
        App->>API: POST booking request (JSON: { package_id })
        API->>DB: INSERT INTO bookings (user_id, package_id, status)
        DB-->>API: Success response
        API-->>App: JSON response { success: true, message: "Booking confirmed" }
        App-->>Traveler: Render success toast "Booking successful!"
        Traveler->>Dashboard: Navigate to Dashboard to view booking status
    end
```

### 3. Customer Inquiry & Staff Reply Sequence
```mermaid
sequenceDiagram
    autonumber
    actor Traveler
    participant Contact as contact.php (JS)
    participant API as api/submit-query.php
    participant DB as config/db.php (MySQL)
    actor Staff
    participant StaffPage as staff.php
    
    Traveler->>Contact: Select Package, type question, click "Send Inquiry"
    Contact->>API: POST request (JSON: { package_id, question_text })
    API->>DB: INSERT INTO queries (status = 'Pending')
    DB-->>API: Success
    API-->>Contact: JSON response { success: true }
    Contact-->>Traveler: Display success toast alert
    
    %% Staff Reply Part
    Staff->>StaffPage: Load staff workspace
    StaffPage->>DB: Query queries table where status = 'Pending'
    DB-->>StaffPage: Return pending questions list
    StaffPage-->>Staff: Render questions with text reply fields
    Staff->>StaffPage: Input response, click "Reply"
    StaffPage->>API: POST response (JSON: { query_id, answer_text })
    API->>DB: UPDATE queries SET answer_text = ?, status = 'Answered'
    DB-->>API: Success
    API-->>StaffPage: JSON response { success: true }
    StaffPage-->>Staff: Update UI badge status to "Answered"
```

---

## 🗄️ Relational Database Schema

The database `globetrek_db` contains five tables linked with appropriate constraints and cascading deletions:

```plaintext
1. users: Stores travelers, travel agents, and administrators.
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - role (VARCHAR, CHECK: customer, staff, admin)
   - username (VARCHAR, UNIQUE)
   - password (VARCHAR)

2. packages: Stores travel tour package parameters.
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - destination (VARCHAR)
   - price (DECIMAL)
   - description (TEXT)
   - likes_count (INT)
   - image_url (VARCHAR)

3. saved_packages: Many-to-Many link tracking traveler bookmarks.
   - user_id (INT, FOREIGN KEY referencing users(id) ON DELETE CASCADE)
   - package_id (INT, FOREIGN KEY referencing packages(id) ON DELETE CASCADE)
   - saved_at (TIMESTAMP)
   - PRIMARY KEY (user_id, package_id)

4. queries: Relational table storing inquiries and staff resolutions.
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - user_id (INT, FOREIGN KEY referencing users(id) ON DELETE CASCADE)
   - package_id (INT, FOREIGN KEY referencing packages(id) ON DELETE CASCADE)
   - question_text (TEXT)
   - answer_text (TEXT, DEFAULT NULL)
   - status (VARCHAR, CHECK: Pending, Answered)
   - created_at (TIMESTAMP)

5. bookings: Relational table logging traveler package reservations.
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - user_id (INT, FOREIGN KEY referencing users(id) ON DELETE CASCADE)
   - package_id (INT, FOREIGN KEY referencing packages(id) ON DELETE CASCADE)
   - status (VARCHAR, CHECK: Pending, Confirmed, Cancelled, DEFAULT Confirmed)
   - booking_date (TIMESTAMP)
```

---

## 🚀 Setup & Installation Instructions

To deploy GlobeTrek Adventures locally using Apache + MySQL (e.g. XAMPP):

1. **Clone the repository**:
   Clone the code inside the XAMPP web root directory:
   `C:\xampp\htdocs\html_asna`

2. **Configure Database**:
   - Open XAMPP Control Panel and start **Apache** and **MySQL**.
   - Open phpMyAdmin (`http://localhost/phpmyadmin`) or MySQL Command Line.
   - Import the [schema.sql](schema.sql) file to create and seed `globetrek_db`.
     ```sql
     source C:/xampp/htdocs/html_asna/schema.sql;
     ```

3. **Establish Credentials Connection**:
   - Check the connection parameters in `config/db.php`. By default, it connects on `127.0.0.1` (localhost) with standard XAMPP configuration:
     - Username: `root`
     - Password: `""` (Empty string)

4. **Launch Application**:
   - Navigate to `http://localhost/html_asna/index.php` in your web browser.

---

## 🔑 Default Seed Credentials for Testing

Use the following seeded accounts to verify the different role access and dashboard controls:

| Role | Username | Password | Access Dashboard | Key Features to Test |
| :--- | :--- | :--- | :--- | :--- |
| **Guest** | *No Log In* | *No Password* | `index.php` / `packages.php` | Browse catalog, search packages. Clicking Like/Book/Star/Ask displays login warning toast. |
| **Traveler (Customer)** | `traveler_srilanka` | `traveler123` | `dashboard.php` | Book packages, toggle likes, bookmark tours, submit Q&A package inquiries. View active bookings and query reply statuses. |
| **Agency Staff** | `staff_negombo` | `staff123` | `staff.php` | Toggle booking status select dropdowns, reply inline to traveler inquiries, edit catalog package pricing/details. |
| **Administrator** | `admin_globetrek` | `admin123` | `admin.php` | View analytical stat cards. Create/delete staff accounts, create/delete tour packages. |
