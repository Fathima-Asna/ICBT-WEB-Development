# 🌍 GlobeTrek Adventures — ICBT Web Development Project

> **A premium Travel & Tourism web application built for the ICBT Web Development module.**  
> Two-phase architecture: **Phase 1** — Static HTML/CSS Frontend | **Phase 2** — Dynamic Java Servlet + JSP Backend

---

## 📑 Table of Contents

- [Project Overview](#-project-overview)
- [Architecture](#-architecture)
- [Phase 1 — Static Frontend (HTML + CSS)](#-phase-1--static-frontend-html--css)
- [Phase 2 — Dynamic Backend (Java EE)](#-phase-2--dynamic-backend-java-ee)
- [Folder Structure](#-folder-structure)
- [Technology Stack](#-technology-stack)
- [How to Run](#-how-to-run)
- [Default Login Credentials](#-default-login-credentials)
- [Features Summary](#-features-summary)
- [Screenshots & Pages](#-screenshots--pages)
- [Design Highlights](#-design-highlights)
- [Author](#-author)

---

## 🎯 Project Overview

**GlobeTrek Adventures** is a fictional premium travel and tourism agency website. The project demonstrates a full-stack web development workflow from designing a pixel-perfect static frontend to building a fully functional dynamic backend with authentication, role-based dashboards, booking management, and flat-file data persistence.

The project is divided into **two phases**:

| Phase | Description | Technologies |
|-------|-------------|-------------|
| **Phase 1** | Static multi-page website with premium UI/UX | HTML5, CSS3 (no JavaScript, no frameworks) |
| **Phase 2** | Dynamic backend with authentication, RBAC, CRUD | Java Servlets, JSP, JSTL, Maven, Tomcat |

---

## 🏗 Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                     CLIENT (Browser)                         │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐│
│  │index.html│ │login.html│ │signup.html│ │packages/accom/...││
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘│
│            Static HTML + CSS (Phase 1)                       │
└──────────────────────────────────────────────────────────────┘
                           │
                    ┌──────▼──────┐
                    │  Apache     │
                    │  Tomcat 9   │
                    └──────┬──────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│               GlobeTrekWeb (Phase 2 — WAR)                   │
│                                                              │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ FILTERS           │ SERVLETS                             ││
│  │ ┌───────────────┐ │ ┌──────────────┐ ┌────────────────┐ ││
│  │ │  AuthFilter    │ │ │ AuthServlet  │ │ BookingServlet │ ││
│  │ │ (RBAC Guard)  │ │ │ /auth        │ │ /book          │ ││
│  │ └───────────────┘ │ ├──────────────┤ ├────────────────┤ ││
│  │                   │ │ AdminServlet │ │ LogoutServlet  │ ││
│  │                   │ │ /admin/action│ │ /logout        │ ││
│  │                   │ └──────────────┘ └────────────────┘ ││
│  └──────────────────────────────────────────────────────────┘│
│                                                              │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ JSP VIEWS                                                ││
│  │ login.jsp · signup.jsp · index.jsp                       ││
│  │ customer-dashboard.jsp · staff-dashboard.jsp             ││
│  │ admin-dashboard.jsp                                      ││
│  └──────────────────────────────────────────────────────────┘│
│                                                              │
│  ┌──────────────────────────────────────────────────────────┐│
│  │ DATA LAYER (Flat-File I/O — No Database)                 ││
│  │ WEB-INF/data/users.txt                                   ││
│  │ WEB-INF/data/bookings.txt                                ││
│  └──────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────┘
```

---

## 🎨 Phase 1 — Static Frontend (HTML + CSS)

> **Pure HTML5 + CSS3 — Zero JavaScript, Zero CSS Frameworks**

### Pages

| Page | File | Description |
|------|------|-------------|
| **Homepage** | `index.html` | Hero banner, company profile (Why Choose Us), featured adventures preview, newsletter CTA |
| **Tour Packages** | `packages.html` | 3 curated packages (Swiss Alps, Bali, Kyoto) with **pure CSS tab filtering** by category (Alpine/Tropical/Cultural) |
| **Accommodations** | `accommodations.html` | 3 luxury stays (Mountain Chalet, Beach Sanctuary, Eco Treehouse) with **pure CSS tab filtering** (Mountain/Beach/Eco) |
| **Contact Us** | `contact.html` | Contact info cards (Phone, Email, Address), CSS map placeholder, multi-field inquiry form |
| **Login** | `login.html` | Split-screen auth layout with immersive travel imagery, email/password form, remember me checkbox |
| **Sign Up** | `signup.html` | Split-screen registration with first/last name, email, password (with confirm), travel style dropdown, T&C checkbox |

### CSS Design System (`style.css` — ~1,700 lines)

- **Design Tokens**: Custom properties for colors, gradients, shadows, typography, spacing, motion
- **Premium Color Palette**: Deep Lagoon Teal (`#0D7377`), Crimson Sunset (`#F05D5E`), Luxury Gold (`#D4AC0D`)
- **Typography**: Google Fonts — `Inter` (sans-serif body) + `Playfair Display` (serif headings)
- **Glassmorphism**: Frosted glass effects on header, badges, and newsletter banners
- **Micro-animations**: Hero parallax, card hover lifts, shimmer button effects, floating orbs
- **Pure CSS Filtering**: Radio button hack for filtering packages/stays without JavaScript
- **Responsive Design**: Mobile hamburger menu, fluid grid layouts, clamp-based typography
- **Custom Form Styling**: Gradient checkboxes, focus ring states, inline validation indicators

---

## ⚙ Phase 2 — Dynamic Backend (Java EE)

> **Java Servlets + JSP + JSTL on Apache Tomcat — File-based data persistence (no database)**

### Backend Components

#### Servlets (`com.globetrek.servlet`)

| Servlet | URL Mapping | Method | Purpose |
|---------|-------------|--------|---------|
| **AuthServlet** | `/auth` | POST | Handles login (validate credentials → create session → redirect to role dashboard) and signup (validate → append to `users.txt` → auto-login) |
| **LogoutServlet** | `/logout` | GET/POST | Invalidates the HttpSession and redirects to login page |
| **BookingServlet** | `/book` | POST | Processes customer booking submissions: validates fields, generates unique booking ID (`GT-XXXXXXXX`), appends to `bookings.txt` |
| **AdminServlet** | `/admin/action` | POST | Admin operations: Add Staff user, Delete user, Update booking status (Pending/Confirmed/Cancelled/Completed) |

#### Filter (`com.globetrek.filter`)

| Filter | Purpose |
|--------|---------|
| **AuthFilter** | Intercepts requests to protected JSPs and servlets. Enforces **Role-Based Access Control (RBAC)**: Admin-only, Staff-or-Admin, Customer-only, Any-authenticated |

#### JSP Views

| JSP | Description |
|-----|-------------|
| `index.jsp` | Public homepage (mirrors Phase 1 `index.html`) |
| `login.jsp` | Login form posting to `/auth?action=login` with EL error display |
| `signup.jsp` | Registration form posting to `/auth?action=signup` with field repopulation on error |
| `customer-dashboard.jsp` | Customer portal: Book a tour, view personal booking history |
| `staff-dashboard.jsp` | Staff portal: View all bookings, update booking statuses |
| `admin-dashboard.jsp` | Admin portal: Manage all users (add staff, delete users), manage all bookings |
| `dashboard.css` | Dedicated stylesheet for all three dashboard pages |

### Data Storage (Flat-File I/O)

| File | Format | Description |
|------|--------|-------------|
| `WEB-INF/data/users.txt` | `email\|password\|role\|firstName\|lastName\|travelStyle` | User accounts (Admin, Staff, Customer) |
| `WEB-INF/data/bookings.txt` | `bookingId\|email\|package\|destination\|date\|travelers\|notes\|status\|submittedAt` | Booking records |

### Role-Based Access Control (RBAC)

| Role | Can Access | Capabilities |
|------|-----------|-------------|
| **Admin** | Admin Dashboard | Add/delete staff, delete customers, manage all bookings, update booking status |
| **Staff** | Staff Dashboard | View all bookings, update booking status |
| **Customer** | Customer Dashboard | Create bookings, view personal booking history |

### Security Features

- Session-based authentication with `HttpSession`
- Session fixation prevention (invalidate old session on login)
- HttpOnly session cookies
- 30-minute session timeout
- Server-side input validation on all forms
- Pipe character (`|`) injection prevention for flat-file safety
- AuthFilter guards all protected resources
- Admin cannot delete their own account

---

## 📁 Folder Structure

```
html_asna/
│
├── index.html                    # Homepage (Phase 1)
├── packages.html                 # Tour Packages page
├── accommodations.html           # Curated Stays page
├── contact.html                  # Contact Us page
├── login.html                    # Login page (static)
├── signup.html                   # Signup page (static)
├── style.css                     # Master stylesheet (~52KB, ~1,700 lines)
├── README.md                     # This file
│
└── GlobeTrekWeb/                 # Phase 2 — Java EE Backend
    ├── pom.xml                   # Maven build configuration
    └── src/
        └── main/
            ├── java/com/globetrek/
            │   ├── servlet/
            │   │   ├── AuthServlet.java       # Login & Signup handler
            │   │   ├── LogoutServlet.java      # Session invalidation
            │   │   ├── BookingServlet.java     # Booking form processor
            │   │   └── AdminServlet.java       # Admin CRUD operations
            │   └── filter/
            │       └── AuthFilter.java         # RBAC access control filter
            │
            └── webapp/
                ├── index.jsp                   # Public homepage (JSP)
                ├── login.jsp                   # Dynamic login with EL errors
                ├── signup.jsp                  # Dynamic signup with field repopulation
                ├── customer-dashboard.jsp      # Customer booking portal
                ├── staff-dashboard.jsp         # Staff booking management
                ├── admin-dashboard.jsp         # Full admin control panel
                ├── style.css                   # Frontend styles (copy for WAR)
                ├── dashboard.css               # Dashboard-specific styles
                └── WEB-INF/
                    ├── web.xml                 # Deployment descriptor
                    └── data/
                        ├── users.txt           # User account records
                        └── bookings.txt        # Booking records
```

---

## 🛠 Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Frontend** | HTML5, CSS3 | — |
| **Backend** | Java Servlets, JSP | Java EE 8 (javax.servlet 4.0.1) |
| **Tag Library** | JSTL | 1.2 |
| **Build Tool** | Apache Maven | 3.x |
| **Server** | Apache Tomcat | 9.x (recommended) |
| **JDK** | Java SE | 11+ |
| **Data Storage** | Plain text files | — |
| **Fonts** | Google Fonts | Inter, Playfair Display |

---

## 🚀 How to Run

### Phase 1 — Static Site (No server required)

Simply open any `.html` file in your browser:

```bash
# Open in default browser
start index.html       # Windows
open index.html        # macOS
xdg-open index.html    # Linux
```

### Phase 2 — Dynamic Backend (Requires Tomcat)

#### Prerequisites
- **JDK 11+** installed and `JAVA_HOME` set
- **Apache Maven 3.x** installed
- **Apache Tomcat 9.x** installed

#### Build & Deploy

```bash
# 1. Navigate to the backend project
cd GlobeTrekWeb

# 2. Build the WAR file
mvn clean package

# 3. Copy the WAR to Tomcat's webapps directory
cp target/GlobeTrekWeb.war /path/to/tomcat/webapps/

# 4. Start Tomcat
/path/to/tomcat/bin/startup.sh     # Linux/macOS
/path/to/tomcat/bin/startup.bat    # Windows

# 5. Open in browser
# http://localhost:8080/GlobeTrekWeb/
```

---

## 🔐 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@globetrek.com` | `Admin@123` |
| **Staff** | `staff@globetrek.com` | `Staff@123` |
| **Customer** | *(Register via Sign Up page)* | *(Your chosen password)* |

---

## ✨ Features Summary

### Frontend Features (Phase 1)
- ✅ Fully responsive multi-page website (6 pages)
- ✅ Premium glassmorphism design with micro-animations
- ✅ Pure CSS package/accommodation filtering (no JavaScript)
- ✅ CSS checkbox hack for responsive mobile hamburger menu
- ✅ Custom styled form elements (checkboxes, select dropdowns, focus states)
- ✅ CSS-only inline form validation indicators
- ✅ SVG iconography throughout (no icon library dependencies)
- ✅ Newsletter subscription CTA banners
- ✅ Animated hero section with floating orbs and gradient overlays
- ✅ Split-screen authentication layouts with immersive travel imagery
- ✅ Semantic HTML5 structure with ARIA labels for accessibility
- ✅ SEO-optimized meta tags on every page

### Backend Features (Phase 2)
- ✅ User Registration (Customer self-signup with auto-login)
- ✅ User Authentication (email/password login with session management)
- ✅ Role-Based Access Control with AuthFilter (Admin / Staff / Customer)
- ✅ Customer Dashboard — Book tours, view personal bookings
- ✅ Staff Dashboard — View all bookings, update booking statuses
- ✅ Admin Dashboard — Full CRUD: add staff, delete users, manage all bookings
- ✅ Flat-file data persistence (no database required)
- ✅ Server-side input validation on all forms
- ✅ Session fixation prevention and secure cookie configuration
- ✅ Graceful error handling with user-friendly messages
- ✅ Form field repopulation on validation errors

---

## 📸 Screenshots & Pages

| Page | Description |
|------|-------------|
| 🏠 **Homepage** | Full-bleed hero with gradient overlays, company profile cards, newsletter CTA |
| 🎒 **Packages** | Swiss Alps ($2,499), Bali ($1,899), Kyoto ($2,199) — filterable by category |
| 🏨 **Accommodations** | Alpine Chalet ($350/night), Beach Villa ($480/night), Eco Pods ($220/night) |
| 📬 **Contact** | Phone, Email, Address cards + styled inquiry form |
| 🔑 **Login** | Split-screen with mountain imagery + email/password form |
| 📝 **Sign Up** | Split-screen with beach imagery + multi-field registration |
| 📊 **Dashboards** | Role-specific dashboards with booking management (Phase 2) |

---

## 🎨 Design Highlights

- **Color Palette**: Deep Lagoon Teal + Crimson Sunset + Luxury Gold
- **Glass Effects**: Frosted glass header navigation and badge overlays
- **Motion Design**: Spring-physics hover animations, shimmer button effects
- **Custom Scrollbar**: Gradient-styled scrollbar matching the brand palette
- **Dark Footer**: Rich charcoal-teal with radial gradient accents
- **Card System**: Multi-elevation shadow system with hover lift effects
- **Typography**: Dual-font system (sans + serif) for visual hierarchy

---

## 👩‍💻 Author Fathima Asna
**ICBT Web Development Module**  
GlobeTrek Adventures — Academic Project

---

> *© 2026 GlobeTrek Adventures Ltd. All rights reserved.*
