<%@ page contentType="text/html; charset=UTF-8" language="java" import="java.io.*, java.util.*, java.nio.charset.StandardCharsets" %>
<%
    // ── Security Guard ─────────────────────────────────────────────────────────
    HttpSession sess = request.getSession(false);
    if (sess == null || !"Customer".equals(sess.getAttribute("userRole"))) {
        response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
        return;
    }

    String userFirstName = (String) sess.getAttribute("userFirstName");
    String userLastName  = (String) sess.getAttribute("userLastName");
    String userEmail     = (String) sess.getAttribute("userEmail");
    String userStyle     = (String) sess.getAttribute("userStyle");
    if (userFirstName == null) userFirstName = "Traveler";
    if (userStyle     == null) userStyle     = "general";

    // ── Read query params for success/error feedback ──────────────────────────
    String successParam = request.getParameter("success");
    String errorParam   = request.getParameter("error");
    String bookingId    = request.getParameter("id");
    boolean isWelcome   = "true".equals(request.getParameter("welcome"));

    // ── Read this customer's bookings from bookings.txt ───────────────────────
    String dataDir = application.getRealPath("/WEB-INF/data/");
    List<String[]> myBookings = new ArrayList<>();
    File bookingsFile = new File(dataDir, "bookings.txt");
    if (bookingsFile.exists()) {
        try (BufferedReader br = new BufferedReader(
                new InputStreamReader(new FileInputStream(bookingsFile), StandardCharsets.UTF_8))) {
            String line;
            while ((line = br.readLine()) != null) {
                line = line.trim();
                if (line.isEmpty() || line.startsWith("#")) continue;
                String[] parts = line.split("\\|", -1);
                if (parts.length >= 9 && parts[1].equalsIgnoreCase(userEmail)) {
                    myBookings.add(parts);
                }
            }
        } catch (IOException e) { /* silently skip on read error */ }
    }

    // Packages data (static — the 4 core GlobeTrek packages)
    String[][] packages = {
        {"Swiss Alps Trek",    "Switzerland", "$3,299", "10 Days", "alpine",
         "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&auto=format&fit=crop",
         "Expert glacier hikes, mountain railway journeys, and luxury chalet stays above the clouds."},
        {"Bali & Gili Islands","Indonesia",   "$2,499", "8 Days",  "tropical",
         "https://images.unsplash.com/photo-1537953773345-d172ccf13cf4?w=600&auto=format&fit=crop",
         "Private beach villas, underwater coral gardens, and sacred temple sunrise ceremonies."},
        {"Ancient Kyoto",      "Japan",       "$2,899", "7 Days",  "cultural",
         "https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=600&auto=format&fit=crop",
         "Geisha districts, bamboo forests, imperial palaces, and traditional tea house experiences."},
        {"Patagonia Expedition","Argentina",  "$4,199", "12 Days", "eco",
         "https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=600&auto=format&fit=crop",
         "Torres del Paine, glacial trekking, condor spotting, and eco-lodge wilderness stays."}
    };
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | GlobeTrek Adventures</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/style.css">
    <link rel="stylesheet" href="<%= request.getContextPath() %>/dashboard.css">
</head>
<body>
<div class="dash-wrapper">

    <!-- ══════════════════════════════════════════════
         SIDEBAR
         ══════════════════════════════════════════════ -->
    <aside class="dash-sidebar" role="navigation" aria-label="Customer dashboard navigation">
        <a href="<%= request.getContextPath() %>/index.jsp" class="sidebar-brand">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm1-13h-2v4H7v2h4v4h2v-4h4v-2h-4V7z"/></svg>
            <div class="sidebar-brand-name">GlobeTrek<span>Adventures</span></div>
        </a>

        <div class="sidebar-role-badge role-customer">Customer</div>

        <div class="sidebar-user">
            <div class="sidebar-user-name"><%= userFirstName %> <%= userLastName %></div>
            <div class="sidebar-user-email"><%= userEmail %></div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-section-label">Navigation</div>
            <a href="#packages-section" class="sidebar-nav-item active">
                <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.86.5 13.34.5c-1.3 0-2.43.52-3.34 1.34C9.1 1.02 7.96.5 6.66.5 4.14.5 2 2.54 2 4.66c0 .46.11.9.18 1.34H0v14h24V6h-4zm-6.67-3.83c1.06 0 1.84.78 1.84 1.83s-.78 1.84-1.84 1.84c-1.06 0-1.83-.78-1.83-1.84s.77-1.83 1.83-1.83zM6.66 3.83c1.06 0 1.84.78 1.84 1.84s-.78 1.84-1.84 1.84c-1.06 0-1.83-.78-1.83-1.84s.77-1.84 1.83-1.84zM2 18V8h8v10H2zm10 0V8h10v10H12z"/></svg>
                Browse Packages
            </a>
            <a href="#book-section" class="sidebar-nav-item">
                <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                Book a Trip
            </a>
            <a href="#mybookings-section" class="sidebar-nav-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                My Bookings
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<%= request.getContextPath() %>/logout" class="sidebar-logout">
                <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- ══════════════════════════════════════════════
         MAIN CONTENT
         ══════════════════════════════════════════════ -->
    <main class="dash-main">

        <!-- Welcome Strip -->
        <div class="dash-hero-strip">
            <div class="dash-hero-greeting">
                Welcome back, <span><%= userFirstName %>!</span>
            </div>
            <p class="dash-hero-tagline">
                Ready for your next adventure? Browse our handcrafted packages below and book your dream journey.
            </p>
        </div>

        <!-- ── Feedback Banners ─────────────────────────────────────── -->
        <% if (isWelcome) { %>
        <div class="dash-alert dash-alert--success" id="welcome-banner" role="alert">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            Welcome to GlobeTrek! Your account is all set. Start exploring below.
        </div>
        <% } %>

        <% if ("booked".equals(successParam)) { %>
        <div class="dash-alert dash-alert--success" id="booking-success-banner" role="alert">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            Booking submitted successfully! Your reference: <strong><%= bookingId != null ? bookingId : "" %></strong>. Our team will confirm it shortly.
        </div>
        <% } %>

        <% if (errorParam != null) {
            String errMsg = "An error occurred. Please try again.";
            if ("missing_fields".equals(errorParam))     errMsg = "All booking fields are required. Please fill in the form completely.";
            if ("invalid_travelers".equals(errorParam))  errMsg = "Number of travelers must be between 1 and 50.";
        %>
        <div class="dash-alert dash-alert--error" id="booking-error-banner" role="alert">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <%= errMsg %>
        </div>
        <% } %>

        <!-- ── SECTION 1: Available Packages ──────────────────────── -->
        <section id="packages-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">Available Tour Packages</h2>
                </div>
                <div class="dash-panel-body">
                    <div class="pkg-grid">
                        <% for (String[] pkg : packages) { %>
                        <div class="pkg-card">
                            <div class="pkg-card-img" style="background-image: url('<%= pkg[5] %>');">
                                <span class="pkg-badge"><%= pkg[3] %></span>
                                <span class="pkg-card-img-label"><%= pkg[0] %></span>
                            </div>
                            <div class="pkg-card-body">
                                <div class="pkg-card-price"><%= pkg[2] %> <span>/ person</span></div>
                                <div class="pkg-card-details">
                                    <strong><%= pkg[1] %></strong> &nbsp;·&nbsp; <%= pkg[3] %><br>
                                    <%= pkg[6] %>
                                </div>
                                <!-- Anchor link scrolls to the booking form and pre-selects this package -->
                                <a href="#book-section" class="pkg-book-btn"
                                   onclick="
                                     document.getElementById('pkg-name').value='<%= pkg[0] %>';
                                     document.getElementById('pkg-dest').value='<%= pkg[1] %>';
                                   ">
                                    Book This Package
                                </a>
                            </div>
                        </div>
                        <% } %>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── SECTION 2: Booking Form ─────────────────────────────── -->
        <section id="book-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">Submit a Booking Request</h2>
                </div>
                <div class="dash-panel-body">
                    <form action="<%= request.getContextPath() %>/book" method="POST" class="dash-form" id="booking-form">

                        <div class="dash-form-row">
                            <div class="dash-form-group">
                                <label for="pkg-name">Package Name</label>
                                <input type="text" id="pkg-name" name="packageName" class="dash-form-input"
                                       placeholder="e.g. Swiss Alps Trek" required>
                            </div>
                            <div class="dash-form-group">
                                <label for="pkg-dest">Destination / Country</label>
                                <input type="text" id="pkg-dest" name="destination" class="dash-form-input"
                                       placeholder="e.g. Switzerland" required>
                            </div>
                        </div>

                        <div class="dash-form-row">
                            <div class="dash-form-group">
                                <label for="travel-date">Preferred Travel Date</label>
                                <input type="date" id="travel-date" name="travelDate" class="dash-form-input" required>
                            </div>
                            <div class="dash-form-group">
                                <label for="num-travelers">Number of Travelers</label>
                                <input type="number" id="num-travelers" name="travelers" class="dash-form-input"
                                       min="1" max="50" placeholder="e.g. 2" required>
                            </div>
                        </div>

                        <div class="dash-form-group">
                            <label for="special-notes">Special Requests / Notes</label>
                            <textarea id="special-notes" name="specialNotes" class="dash-form-textarea"
                                      placeholder="Any dietary requirements, accessibility needs, or special occasion notes..."></textarea>
                        </div>

                        <button type="submit" class="dash-form-submit" id="btn-submit-booking">
                            Submit Booking Request
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- ── SECTION 3: My Bookings ──────────────────────────────── -->
        <section id="mybookings-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">My Bookings</h2>
                    <span class="badge badge-customer"><%= myBookings.size() %> booking<%= myBookings.size() != 1 ? "s" : "" %></span>
                </div>
                <div class="dash-panel-body no-pad">
                    <div class="dash-table-wrapper">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Package</th>
                                    <th>Destination</th>
                                    <th>Travel Date</th>
                                    <th>Travelers</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <% if (myBookings.isEmpty()) { %>
                                <tr>
                                    <td colspan="7" class="dash-table-empty">
                                        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                                        No bookings yet. Browse the packages above and submit your first request!
                                    </td>
                                </tr>
                                <% } else {
                                    for (String[] b : myBookings) {
                                        String statusClass = "badge-pending";
                                        if ("Confirmed".equals(b[7]))  statusClass = "badge-confirmed";
                                        if ("Cancelled".equals(b[7]))  statusClass = "badge-cancelled";
                                        if ("Completed".equals(b[7]))  statusClass = "badge-completed";
                                %>
                                <tr>
                                    <td><code><%= b[0] %></code></td>
                                    <td><strong><%= b[2] %></strong></td>
                                    <td><%= b[3] %></td>
                                    <td><%= b[4] %></td>
                                    <td><%= b[5] %></td>
                                    <td><span class="badge <%= statusClass %>"><%= b[7] %></span></td>
                                    <td><small><%= b[8] %></small></td>
                                </tr>
                                <%  }
                                } %>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>
</body>
</html>
