# GlobeTrek Adventures — Decoupled Monorepo Setup

Welcome to the **GlobeTrek Adventures** repository. This codebase has been restructured into a decoupled monorepo architecture to enable seamless, independent deployment of the static frontend and dynamic PHP backend.

---

## 📁 Monorepo Architecture

```text
html_asna/
├── frontend/                 # Static Frontend Application (Deployable to Vercel/Netlify)
│   ├── js/
│   │   ├── auth.js           # Client-side validation policies
│   │   ├── config.js         # Dynamic environment and API Base URL resolver
│   │   └── theme.js          # Premium HSL/CSS3 Theme Switcher
│   ├── accommodations.html   # Lodging grids with client-side text filters
│   ├── contact.html          # Contact form with user verification feedback
│   ├── index.html            # Brand home landing layout
│   ├── join.html             # Unified Sign In & Create Account tab views
│   ├── packages.html         # Custom booking form and live estimator
│   ├── privacy.html          # Legal policy page
│   ├── terms.html            # Service boundaries documentation
│   └── style.css             # Main styling system (Dark mode & layout fixes)
│
├── backend/                  # REST API & Database Processing Engine (Deployable to Back4App)
│   ├── Dockerfile            # Apache/PHP 8.1 container deployment script
│   ├── schema.sql            # Core database schema with secure user & booking tables
│   ├── db_config.php         # PDO handler, TiDB connection, and CORS configurations
│   ├── check_session.php     # Session authorization verifier API
│   ├── process_login.php     # User authenticate endpoint
│   ├── process_register.php  # User registration and uniqueness validation
│   ├── process_booking.php   # Secure package booking intake API
│   └── logout.php            # Active session termination endpoint
│
└── README.md                 # Project Setup & Configuration Documentation (This file)
```

---

## 🚀 Decoupled Deployment Model

### 1. Frontend: Vercel (or static host)
The static frontend files in `/frontend` are completely self-contained. They interact with the backend API exclusively via AJAX `fetch` calls and HTML form submissions.
*   **Production URL Config**: Defined in `frontend/js/config.js` (`https://globetrek-backend.back4app.io`).
*   The `config.js` script dynamically rewrites relative form actions and backend endpoints depending on whether you are running locally or on the production server.

### 2. Backend: Back4App Containers
The PHP backend in `/backend` runs inside an Apache-PHP container defined by the `Dockerfile`.
*   **Dockerfile Configuration**: Configures `php:8.1-apache` with `pdo_mysql` extensions to connect securely to the database.
*   **CORS (Cross-Origin Resource Sharing)**: Fully configured to allow cross-origin requests from the Vercel frontend. It handles preflight `OPTIONS` requests at the gateway of each PHP route.
*   **Absolute Redirections**: Redirect headers (`Location:`) point to the absolute URL of the frontend's server domain (`FRONTEND_URL` from `db_config.php`) to prevent 404s inside the container network.

---

## 🛠️ Local Development Setup

To run and test the complete system locally:

### 1. Database Setup
1. Start your local MySQL/MariaDB server (or use a remote TiDB Serverless cluster).
2. Import the schema script located at `backend/schema.sql` into a database named `globetrek_db`.
3. Configure your credentials inside `backend/db_config.php`:
    ```php
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'globetrek_db');
    ```

### 2. Start the Backend API Server
Navigate to the `/backend` folder and boot up a local PHP server on port `8000`:
```bash
cd backend
php -S localhost:8000
```
This is recognized by `frontend/js/config.js` and `backend/db_config.php` as the local API host.

### 3. Start the Frontend Development Server
Start a local static server inside `/frontend` on port `5500` (e.g., using VS Code Live Server extension or another tool):
```bash
# E.g., using live-server npm package
cd frontend
npx live-server --port=5500
```
Open `http://localhost:5500` in your web browser.

---

## 🔒 Security Features

*   **SQL Injection Prevention**: All queries to TiDB MySQL are executed using PDO Prepared Statements.
*   **Cryptographic Passwords**: Passwords are securely hashed using `PASSWORD_BCRYPT` with high work factors.
*   **Concurrent Session Lock**: Prevents a single username from being logged in simultaneously on two separate devices. Logging in on a new device will invalidate the previous active session using `session_token` table verification.
