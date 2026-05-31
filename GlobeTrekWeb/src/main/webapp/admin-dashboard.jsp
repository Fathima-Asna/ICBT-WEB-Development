<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%
    // ── Security Guard: Admin ONLY ─────────────────────────────────────────
    HttpSession sess = request.getSession(false);
    if (sess == null || !"Admin".equals(sess.getAttribute("userRole"))) {
        response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
        return;
    }

    String adminName  = (String) sess.getAttribute("userName");
    String adminEmail = (String) sess.getAttribute("userEmail");
    if (adminName == null) adminName = "Administrator";

    // ── Feedback query params ──────────────────────────────────────────────
    String successParam = request.getParameter("success");
    String errorParam   = request.getParameter("error");
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | GlobeTrek Adventures</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/style.css">
    <link rel="stylesheet" href="<%= request.getContextPath() %>/dashboard.css">
</head>
<body>
<div class="dash-wrapper">

    <!-- ══════════════════════════════════════════════
         SIDEBAR
         ══════════════════════════════════════════════ -->
    <aside class="dash-sidebar" role="navigation" aria-label="Admin dashboard navigation">
        <a href="<%= request.getContextPath() %>/index.jsp" class="sidebar-brand">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm1-13h-2v4H7v2h4v4h2v-4h4v-2h-4V7z"/></svg>
            <div class="sidebar-brand-name">GlobeTrek<span>Adventures</span></div>
        </a>

        <div class="sidebar-role-badge role-admin">Administrator</div>

        <div class="sidebar-user">
            <div class="sidebar-user-name"><%= adminName %></div>
            <div class="sidebar-user-email"><%= adminEmail %></div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-section-label">Overview</div>
            <a href="#stats-section" class="sidebar-nav-item active">
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                Stats Overview
            </a>
            <div class="sidebar-nav-section-label">User Management</div>
            <a href="#users-section" class="sidebar-nav-item">
                <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                All Users
            </a>
            <a href="#add-staff-section" class="sidebar-nav-item">
                <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                Add Staff Member
            </a>
            <div class="sidebar-nav-section-label">Bookings</div>
            <a href="#bookings-section" class="sidebar-nav-item">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                All Bookings
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
                Administration Panel — <span>GlobeTrek</span>
            </div>
            <p class="dash-hero-tagline">
                Full system control. Manage users, staff accounts, and all customer bookings below.
            </p>
        </div>

        <!-- ── Feedback Banners ─────────────────────────────────────── -->
        <c:if test="${param.success == 'staff_added'}">
            <div class="dash-alert dash-alert--success" role="alert">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                New staff member added successfully.
            </div>
        </c:if>
        <c:if test="${param.success == 'user_deleted'}">
            <div class="dash-alert dash-alert--success" role="alert">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                User account deleted successfully.
            </div>
        </c:if>
        <c:if test="${param.success == 'status_updated'}">
            <div class="dash-alert dash-alert--success" role="alert">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                Booking status updated successfully.
            </div>
        </c:if>

        <c:if test="${param.error != null}">
            <div class="dash-alert dash-alert--error" role="alert">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                <c:choose>
                    <c:when test="${param.error == 'staff_fields_required'}">All staff fields are required.</c:when>
                    <c:when test="${param.error == 'staff_invalid_email'}">Invalid email address for staff account.</c:when>
                    <c:when test="${param.error == 'staff_weak_password'}">Staff password must be at least 8 characters.</c:when>
                    <c:when test="${param.error == 'staff_email_exists'}">An account with this email already exists.</c:when>
                    <c:when test="${param.error == 'cannot_delete_self'}">You cannot delete your own admin account.</c:when>
                    <c:when test="${param.error == 'user_not_found'}">User not found in the system.</c:when>
                    <c:when test="${param.error == 'booking_not_found'}">Booking ID not found.</c:when>
                    <c:when test="${param.error == 'invalid_status'}">Invalid booking status value.</c:when>
                    <c:when test="${param.error == 'db_error'}">A database error occurred. Please try again.</c:when>
                    <c:otherwise>An unexpected error occurred.</c:otherwise>
                </c:choose>
            </div>
        </c:if>

        <!-- ── Database error message (if any) ─────────────────────── -->
        <c:if test="${not empty errorMsg}">
            <div class="dash-alert dash-alert--error" role="alert">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                ${errorMsg}
            </div>
        </c:if>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 1: Stats Overview
             ══════════════════════════════════════════════════════════ -->
        <section id="stats-section">
            <div class="dash-stats-grid" style="margin-bottom:28px;">
                <div class="stat-card">
                    <div class="stat-card-icon">
                        <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    </div>
                    <div class="stat-card-value">${totalUsers}</div>
                    <div class="stat-card-label">Total Users</div>
                </div>
                <div class="stat-card accent-gold">
                    <div class="stat-card-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="stat-card-value">${staffCount}</div>
                    <div class="stat-card-label">Staff Members</div>
                </div>
                <div class="stat-card accent-teal">
                    <div class="stat-card-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    </div>
                    <div class="stat-card-value">${customerCount}</div>
                    <div class="stat-card-label">Customers</div>
                </div>
                <div class="stat-card accent-sunset">
                    <div class="stat-card-icon">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    </div>
                    <div class="stat-card-value">${totalBookings}</div>
                    <div class="stat-card-label">Total Bookings</div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 2: All Registered Users Table
             ══════════════════════════════════════════════════════════ -->
        <section id="users-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">Registered Users</h2>
                    <span class="badge badge-customer">${totalUsers} accounts</span>
                </div>
                <div class="dash-panel-body no-pad">
                    <div class="dash-table-wrapper">
                        <table class="dash-table" id="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${empty userList}">
                                        <tr>
                                            <td colspan="6" class="dash-table-empty">
                                                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                                No users found in the database.
                                            </td>
                                        </tr>
                                    </c:when>
                                    <c:otherwise>
                                        <c:forEach var="user" items="${userList}">
                                            <tr>
                                                <td>${user.id}</td>
                                                <td><strong>${user.fullName}</strong></td>
                                                <td>${user.email}</td>
                                                <td>
                                                    <c:choose>
                                                        <c:when test="${user.role == 'Admin'}">
                                                            <span class="badge badge-admin">${user.role}</span>
                                                        </c:when>
                                                        <c:when test="${user.role == 'Staff'}">
                                                            <span class="badge badge-staff">${user.role}</span>
                                                        </c:when>
                                                        <c:otherwise>
                                                            <span class="badge badge-customer">${user.role}</span>
                                                        </c:otherwise>
                                                    </c:choose>
                                                </td>
                                                <td><small>${user.createdAt}</small></td>
                                                <td>
                                                    <%-- Prevent admin from deleting themselves --%>
                                                    <c:choose>
                                                        <c:when test="${user.email == adminEmail}">
                                                            <small style="color:#6B8E92;">(you)</small>
                                                        </c:when>
                                                        <c:otherwise>
                                                            <form action="<%= request.getContextPath() %>/admin/action" method="POST" style="display:inline;">
                                                                <input type="hidden" name="action"   value="deleteUser">
                                                                <input type="hidden" name="targetId" value="${user.id}">
                                                                <button type="submit" class="btn-table-action delete">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </c:otherwise>
                                                    </c:choose>
                                                </td>
                                            </tr>
                                        </c:forEach>
                                    </c:otherwise>
                                </c:choose>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 3: Add Staff Member
             ══════════════════════════════════════════════════════════ -->
        <section id="add-staff-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">Add New Staff Member</h2>
                </div>
                <div class="dash-panel-body">
                    <p style="margin-bottom:18px;color:#6B8E92;font-size:0.90rem;">
                        Create a new Staff account. Staff members can view and manage customer bookings but cannot access the Admin panel.
                    </p>
                    <form action="<%= request.getContextPath() %>/admin/action" method="POST" class="dash-form" id="add-staff-form">
                        <input type="hidden" name="action" value="addStaff">

                        <div class="dash-form-group">
                            <label for="staff-full-name">Full Name</label>
                            <input type="text" id="staff-full-name" name="staffFullName"
                                   class="dash-form-input" placeholder="e.g. Alex Johnson" required>
                        </div>

                        <div class="dash-form-row">
                            <div class="dash-form-group">
                                <label for="staff-email">Email Address</label>
                                <input type="email" id="staff-email" name="staffEmail"
                                       class="dash-form-input" placeholder="e.g. alex@globetrek.com" required>
                            </div>
                            <div class="dash-form-group">
                                <label for="staff-password">Temporary Password</label>
                                <input type="password" id="staff-password" name="staffPassword"
                                       class="dash-form-input" placeholder="Min. 8 characters" minlength="8" required>
                            </div>
                        </div>

                        <button type="submit" class="dash-form-submit" id="btn-add-staff">
                            Create Staff Account
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 4: All Bookings Overview
             ══════════════════════════════════════════════════════════ -->
        <section id="bookings-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">All Bookings</h2>
                    <span class="badge badge-customer">${totalBookings} total</span>
                </div>
                <div class="dash-panel-body no-pad">
                    <div class="dash-table-wrapper">
                        <table class="dash-table" id="bookings-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Destination</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${empty bookingList}">
                                        <tr>
                                            <td colspan="8" class="dash-table-empty">
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                                No bookings recorded yet.
                                            </td>
                                        </tr>
                                    </c:when>
                                    <c:otherwise>
                                        <c:forEach var="booking" items="${bookingList}">
                                            <tr>
                                                <td><code>${booking.id}</code></td>
                                                <td>
                                                    <strong>${booking.customerName}</strong>
                                                    <small>${booking.customerEmail}</small>
                                                </td>
                                                <td><strong>${booking.packageName}</strong></td>
                                                <td>${booking.destination}</td>
                                                <td><strong>$${booking.price}</strong></td>
                                                <td>
                                                    <c:choose>
                                                        <c:when test="${booking.status == 'Pending'}">
                                                            <span class="badge badge-pending">${booking.status}</span>
                                                        </c:when>
                                                        <c:when test="${booking.status == 'Confirmed'}">
                                                            <span class="badge badge-confirmed">${booking.status}</span>
                                                        </c:when>
                                                        <c:when test="${booking.status == 'Cancelled'}">
                                                            <span class="badge badge-cancelled">${booking.status}</span>
                                                        </c:when>
                                                        <c:when test="${booking.status == 'Completed'}">
                                                            <span class="badge badge-completed">${booking.status}</span>
                                                        </c:when>
                                                    </c:choose>
                                                </td>
                                                <td><small>${booking.bookingDate}</small></td>
                                                <td>
                                                    <form action="<%= request.getContextPath() %>/admin/action" method="POST" style="display:inline-flex;gap:4px;align-items:center;">
                                                        <input type="hidden" name="action"    value="updateStatus">
                                                        <input type="hidden" name="bookingId" value="${booking.id}">
                                                        <select name="newStatus" class="dash-form-select" style="padding:4px 8px;font-size:0.75rem;border-radius:6px;">
                                                            <option value="Pending"   ${booking.status == 'Pending'   ? 'selected' : ''}>Pending</option>
                                                            <option value="Confirmed" ${booking.status == 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                                            <option value="Cancelled" ${booking.status == 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                                            <option value="Completed" ${booking.status == 'Completed' ? 'selected' : ''}>Completed</option>
                                                        </select>
                                                        <button type="submit" class="btn-table-action" style="background:#0D7377;color:#fff;border-color:#0D7377;">
                                                            Save
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        </c:forEach>
                                    </c:otherwise>
                                </c:choose>
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
