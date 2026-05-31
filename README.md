# 🌍 GlobeTrek Adventures — ICBT Web Development Project

> **A premium Travel & Tourism web application built for the ICBT Web Development module.**  
> Built with a **100% JSP-Free Architecture**: Dynamic, database-driven server-side rendering using **Pure HTML/CSS on the Frontend** and **Strict Java Servlets, JDBC, and SQL on the Backend**.

---

## 📑 Table of Contents

- [Project Overview](#-project-overview)
- [System Architecture](#-system-architecture)
- [Template Rendering Engine](#-template-rendering-engine)
- [Database Schema & Seed Data](#-database-schema--seed-data)
- [Folder Structure](#-folder-structure)
- [Technology Stack](#-technology-stack)
- [Features Summary](#-features-summary)
- [Default Login Credentials](#-default-login-credentials)
- [Deployment & How to Run](#-deployment--how-to-run)
- [Design Highlights](#-design-highlights)
- [Author](#-author)

---

## 🎯 Project Overview

**GlobeTrek Adventures** is a fictional premium travel and tourism agency website designed to deliver a modern, interactive booking experience. 

Moving away from legacy JSP page structures and heavy client-side JavaScript frameworks, this project employs a professional, secure **server-side template injection pattern**:
1. The **Frontend** consists of pure static `.html` and `.css` pages optimized for visual excellence, performance, and SEO.
2. The **Backend** is built entirely with **Java EE Servlets** and **standard JDBC** connecting to a remote **TiDB Serverless MySQL database**.
3. Dynamic database data (such as user profiles, travel packages, and responsive booking grids) is injected directly into static HTML layouts on the fly using a robust Java utility (`TemplateRenderer.java`) before writing standard responses to the browser.

---

## 🏗 System Architecture

The following diagram illustrates how the components interact in our JSP-free stack:

```
┌──────────────────────────────────────────────────────────────┐
│                     CLIENT (Browser)                         │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐│
│  │index.html│ │login.html│ │signup.html│ │packages/accom/...││
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘│
│            Static Premium HTML + CSS                         │
└──────────────────────────────────────────────────────────────┘
                           │
                           │ HTTP GET/POST (Forms / JSON)
                    ┌──────▼──────┐
                    │  Apache     │
                    │  Tomcat 9   │
                    └──────┬──────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│               GlobeTrekWeb (WAR Deployment)                  │
│                                                              │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ FILTERS           │ SERVLETS                             ││
│  │ ┌───────────────┐ │ ┌──────────────┐ ┌────────────────┐ ││
│  │ │  AuthFilter    │ │ │ AuthServlet  │ │ BookingServlet │ ││
│  │ │ (RBAC Guard)  │ │ │ /auth        │ │ /book          │ ││
│  │ └───────────────┘ │ ├──────────────┤ ├────────────────┤ ││
│  │                   │ │ AdminServlet │ │ IndexServlet   │ ││
│  │                   │ │ /admin/action│ │ /index.html    │ ││
│  │                   │ ├──────────────┤ ├────────────────┤ ││
│  │                   │ │ StaffServlet │ │ LoginServlet   │ ││
│  │                   │ │ /staff/action│ │ /login.html    │ ││
│  │                   │ └──────────────┘ └────────────────┘ ││
│  │                   │                                      ││
│  │                   │      TemplateRenderer.java           ││
│  │                   │ (Reads static HTML & injects SQL data)││
│  └───────────────────┴──────────────────────────────────────┘│
│                                                              │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ HTML TEMPLATES (Pure static template assets)             ││
│  │ login.html · signup.html · index.html                    ││
│  │ customer-dashboard.html · staff-dashboard.html            ││
│  │ admin-dashboard.html · packages.html                      ││
│  └──────────────────────────────────────────────────────────┘│
│                                                              │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ DATABASE LAYER (Standard JDBC Connection)                ││
│  │ DBConnection.java ──► MySQL JDBC Driver                   ││
│  │       │                                                  ││
│  └───────┼──────────────────────────────────────────────────┘│
└──────────┼───────────────────────────────────────────────────┘
           │ (Remote TCP Port 4000)
┌──────────▼───────────────────────────────────────────────────┐
│              TiDB Serverless Remote Database                 │
│              Schema: globetrek_db                            │
│              Tables: users, bookings, packages               │
└──────────────────────────────────────────────────────────────┘
```

---

## ⚡ Template Rendering Engine

At the core of the dynamic application is [TemplateRenderer.java](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/src/main/java/com/globetrek/util/TemplateRenderer.java). Instead of compiling heavy JSPs, the Java Servlets load beautiful static templates as standard `String` structures and perform real-time replacements:

```java
// Example: Dynamically inject active customer variables into the static layout
String html = TemplateRenderer.render(getServletContext(), "/customer-dashboard.html");
html = html.replace("{{userFirstName}}", user.getFullName());
html = html.replace("{{bookingsTable}}", generatedTableHtml);
response.getWriter().write(html);
```

### Supported Dynamic Placeholders:
- `{{userFirstName}}`: Displays the logged-in user's personalized name in the dashboard header.
- `{{bookingsTable}}`: Populates the real-time, responsive SQL-driven bookings grid.
- `{{packagesContainer}}`: Renders dynamic cards populated straight from the database.
- `<!-- ALERT_BANNER -->` & `<!-- ERROR_BANNER -->`: Seamlessly injects styled validation or state alerts without breaking layouts.

---

## 🗄 Database Schema & Seed Data

The database resides in a **TiDB Serverless remote cluster** and contains three highly normalized tables. You can review the complete creation script in [schema.sql](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/database/schema.sql).

### Table Structures

#### 1. `users` Table
Stores authentication and access levels for Admins, Staff, and Customers.
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff', 'Customer') DEFAULT 'Customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. `packages` Table
Stores travel packages created, modified, and monitored by administrators and staff.
```sql
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 3. `bookings` Table
Maps customer orders to travel packages, protected via foreign keys with cascading updates.
```sql
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);
```

---

## 📁 Folder Structure

The code is strictly separated to maximize modularity and performance:

```
html_asna/                          # Root Repository
│
├── index.html                      # Landing Page (Static copy)
├── packages.html                   # Packages Page (Static copy)
├── accommodations.html             # Stays Page (Static copy)
├── contact.html                    # Contact Page (Static copy)
├── login.html                      # Login Page (Static copy)
├── signup.html                     # Signup Page (Static copy)
├── style.css                       # Main CSS stylesheet
│
└── GlobeTrekWeb/                   # Maven Core Web Application
    ├── pom.xml                     # Maven project configuration
    ├── database/
    │   └── schema.sql              # Database creation & Seed data
    └── src/main/
        ├── java/com/globetrek/
        │   ├── servlet/
        │   │   ├── IndexServlet.java     # Home page interceptor & CTA manager
        │   │   ├── LoginServlet.java     # Login interface & alert controller
        │   │   ├── AuthServlet.java      # Auth handler (sign-in, registration validation)
        │   │   ├── LogoutServlet.java     # Session invalidator
        │   │   ├── BookingServlet.java    # Dynamic customer bookings handler
        │   │   ├── AdminServlet.java      # Admin actions: user deletion, status updates
        │   │   ├── StaffServlet.java      # Staff metrics & package price updates
        │   │   └── PackageServlet.java    # SQL dynamic destination wildcard filter
        │   ├── filter/
        │   │   └── AuthFilter.java        # Security filter & RBAC Guard
        │   └── util/
        │       ├── DBConnection.java      # Singleton JDBC Driver cluster factory
        │       └── TemplateRenderer.java  # Static page dynamic parser
        │
        └── webapp/
            ├── WEB-INF/
            │   └── web.xml                # Servlet routing & security declaration
            ├── index.html                 # Core Home template
            ├── login.html                 # Core Login template
            ├── signup.html                # Core Signup template
            ├── customer-dashboard.html    # Customer workspace layout
            ├── staff-dashboard.html       # Staff booking dashboard
            ├── admin-dashboard.html       # Admin global configuration panel
            ├── packages.html              # Travel package template
            ├── accommodations.html        # Premium accommodations page
            ├── contact.html               # Contact form page
            └── style.css                  # Premium style styles
```

---

## 🛠 Technology Stack

| Layer | Technology | Specification / Version | Role |
|-------|-----------|---------|---------|
| **Frontend** | HTML5, CSS3 | Standard Semantic Web | Responsive, visually rich interfaces |
| **Backend** | Java Servlets | Java EE 8 (javax.servlet 4.0.1) | Dynamic controllers and template engines |
| **Database** | TiDB Serverless | MySQL 8.0 Protocol Compatible | Secure, clusterized SQL cloud storage |
| **Driver** | JDBC Driver | `mysql-connector-java` 8.0.28 | Establishes low-latency TCP database link |
| **Build Tool** | Apache Maven | Maven 3.8+ | Dependency and target war file packaging |
| **Server** | Apache Tomcat | Tomcat 9.0.x | Web application container |
| **Design** | Google Fonts | Inter & Playfair Display | Immersive typographic systems |

---

## ✨ Features Summary

### 💎 Frontend Features
- **100% Pure HTML/CSS**: Zero JavaScript frameworks for instant page loads.
- **Harmony Color Scheme**: Deep Lagoon Teal (`#0D7377`), Crimson Sunset (`#F05D5E`), and Luxury Gold (`#D4AC0D`).
- **Glassmorphism Design**: Blur backdrop overlays on headers, cards, and warning flags.
- **Pure CSS Dynamic Tab Filters**: CSS radio button toggles filter tour packages and hotels effortlessly.
- **Micro-Animations**: Hover card elevation, button-shimmer effects, and interactive form input transitions.
- **SEO Optimized**: Unique meta descriptors, structured HTML5 headings, and alt labels.

### 🛡 Backend Features
- **100% JDBC Operations**: High speed transactional database actions via `java.sql` prepared statements.
- **Custom Template Replacement Engine**: Injects HTML layout fragments and strings with high speed before returning data.
- **Advanced Auth Guards (RBAC)**: `AuthFilter.java` scans roles in `HttpSession` variables and restricts invalid access automatically.
- **Live Interactive Dashboards**:
  - **Customer Portal**: Displays personal booking histories, real-time prices, and processes instant bookings.
  - **Staff Portal**: Computes metrics (Avg/Max Prices) and allows inline package descriptions/prices to be updated directly in the DB.
  - **Admin Panel**: Manages all user roles, edits statuses (Pending, Confirmed, Completed, Cancelled), and executes safe SQL cascade user deletions.
- **Secure Sessions**: Generates fresh Session IDs to prevent session fixation, applies 30-minute timeouts, and blocks script manipulation.

---

## 🔐 Default Login Credentials

Use the following academic accounts to test the Role-Based dashboards:

| Role | Email | Password | Dashboard Redirect |
|------|-------|----------|---------------------|
| **Admin** | `admin@globetrek.com` | `Admin@123` | `/admin-dashboard.html` |
| **Staff** | `staff@globetrek.com` | `Staff@123` | `/staff-dashboard.html` |
| **Customer** | `john.doe@example.com` | `Customer@123` | `/customer-dashboard.html` |
| **Customer (Register)** | *(Create via Sign Up)* | *(Your custom choice)* | `/customer-dashboard.html` |

---

## 🚀 Deployment & How to Run

### 1. Set Up Database
Connect to your MySQL server (or remote cluster) and run the initialization script to prepare tables and seed data:
```bash
mysql -h <host> -u <user> -p < GlobeTrekWeb/database/schema.sql
```

### 2. Configure JDBC Connection
Open [DBConnection.java](file:///c:/Users/mmhus/OneDrive/Desktop/html_asna/GlobeTrekWeb/src/main/java/com/globetrek/util/DBConnection.java) and modify the remote connection credentials to target your cluster:
```java
private static final String URL  = "jdbc:mysql://<your-tidb-host>:4000/globetrek_db?useSSL=true";
private static final String USER = "<your-username>";
private static final String PASS = "<your-password>";
```

### 3. Package and Deploy
Build the deployment-ready Web ARchive (`.war`) file via Maven:
```bash
cd GlobeTrekWeb
mvn clean package
```

Copy the packaged `.war` to your local Tomcat deployment server:
```bash
cp target/GlobeTrekWeb.war /path/to/tomcat/webapps/
```

### 4. Run Tomcat
Launch your Tomcat application container:
* **Windows**: Run `\path\to\tomcat\bin\startup.bat`
* **Linux/macOS**: Run `sh /path/to/tomcat/bin/startup.sh`

Navigate to the live URL: `http://localhost:8080/GlobeTrekWeb/index.html`.

---

## 👩‍💻 Author Fathima Asna
**ICBT Web Development Module**  
GlobeTrek Adventures — Academic Full-Stack Project

---

> *© 2026 GlobeTrek Adventures Ltd. All rights reserved.*
