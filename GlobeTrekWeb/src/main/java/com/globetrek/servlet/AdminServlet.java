package com.globetrek.servlet;

import com.globetrek.util.DBConnection;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * AdminServlet — Handles all Admin Dashboard operations for GlobeTrek Adventures.
 *
 * Mapped to: /admin/action  (protected by AuthFilter — Admin role only)
 *
 * ┌───────────────────────────────────────────────────────────────────────┐
 * │ GET  /admin/action  → Loads ALL dashboard data (stats, users,       │
 * │                        bookings) as request attributes, then         │
 * │                        forwards to admin-dashboard.jsp               │
 * │                                                                      │
 * │ POST /admin/action  → Processes form submissions:                    │
 * │   action=addStaff      → Insert new Staff user into users table      │
 * │   action=deleteUser    → Delete a user by ID from users table        │
 * │   action=updateStatus  → Update booking status in bookings table     │
 * └───────────────────────────────────────────────────────────────────────┘
 *
 * Data Source: MySQL via standard JDBC (DBConnection singleton)
 * No frameworks — pure Java EE Servlets + JDBC.
 */
public class AdminServlet extends HttpServlet {

    // ========================================================================
    // GET — Load dashboard data and forward to JSP
    // ========================================================================
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ── Security: verify Admin session ────────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || !"Admin".equals(session.getAttribute("userRole"))) {
            response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
            return;
        }

        try {
            Connection conn = DBConnection.getConnection();

            // ── 1. Dashboard Metrics ──────────────────────────────────────────
            int totalUsers    = countQuery(conn, "SELECT COUNT(*) FROM users");
            int totalBookings = countQuery(conn, "SELECT COUNT(*) FROM bookings");
            int staffCount    = countQuery(conn, "SELECT COUNT(*) FROM users WHERE role = 'Staff'");
            int customerCount = countQuery(conn, "SELECT COUNT(*) FROM users WHERE role = 'Customer'");

            request.setAttribute("totalUsers",    totalUsers);
            request.setAttribute("totalBookings", totalBookings);
            request.setAttribute("staffCount",    staffCount);
            request.setAttribute("customerCount", customerCount);

            // ── 2. All Users ──────────────────────────────────────────────────
            List<Map<String, String>> userList = new ArrayList<>();
            String userSQL = "SELECT id, full_name, email, role, created_at FROM users ORDER BY id ASC";

            try (PreparedStatement ps = conn.prepareStatement(userSQL);
                 ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Map<String, String> user = new HashMap<>();
                    user.put("id",        String.valueOf(rs.getInt("id")));
                    user.put("fullName",  rs.getString("full_name"));
                    user.put("email",     rs.getString("email"));
                    user.put("role",      rs.getString("role"));
                    user.put("createdAt", rs.getString("created_at"));
                    userList.add(user);
                }
            }
            request.setAttribute("userList", userList);

            // ── 3. All Bookings (JOIN with users and packages) ────────────────
            List<Map<String, String>> bookingList = new ArrayList<>();
            String bookSQL =
                "SELECT b.id, u.full_name AS customer_name, u.email AS customer_email, " +
                "       p.name AS package_name, p.destination, p.price, " +
                "       b.status, b.booking_date " +
                "FROM bookings b " +
                "JOIN users u    ON b.user_id    = u.id " +
                "JOIN packages p ON b.package_id = p.id " +
                "ORDER BY b.booking_date DESC";

            try (PreparedStatement ps = conn.prepareStatement(bookSQL);
                 ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Map<String, String> booking = new HashMap<>();
                    booking.put("id",            String.valueOf(rs.getInt("id")));
                    booking.put("customerName",  rs.getString("customer_name"));
                    booking.put("customerEmail", rs.getString("customer_email"));
                    booking.put("packageName",   rs.getString("package_name"));
                    booking.put("destination",   rs.getString("destination"));
                    booking.put("price",         String.valueOf(rs.getDouble("price")));
                    booking.put("status",        rs.getString("status"));
                    booking.put("bookingDate",   rs.getString("booking_date"));
                    bookingList.add(booking);
                }
            }
            request.setAttribute("bookingList", bookingList);

        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("errorMsg", "Database error: " + e.getMessage());
        }

        // ── Forward to the JSP view ───────────────────────────────────────────
        request.getRequestDispatcher("/admin-dashboard.jsp").forward(request, response);
    }

    // ========================================================================
    // POST — Handle form submissions (addStaff, deleteUser, updateStatus)
    // ========================================================================
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");

        // ── Security: verify Admin session ────────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || !"Admin".equals(session.getAttribute("userRole"))) {
            response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
            return;
        }

        String action = request.getParameter("action");

        if ("addStaff".equals(action)) {
            handleAddStaff(request, response);
        } else if ("deleteUser".equals(action)) {
            handleDeleteUser(request, response);
        } else if ("updateStatus".equals(action)) {
            handleUpdateStatus(request, response);
        } else {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=unknown_action");
        }
    }

    // ========================================================================
    // ADD STAFF — Insert a new Staff user into MySQL
    // ========================================================================
    private void handleAddStaff(HttpServletRequest request, HttpServletResponse response)
            throws IOException {

        String fullName = sanitize(request.getParameter("staffFullName"));
        String email    = sanitize(request.getParameter("staffEmail"));
        String password = request.getParameter("staffPassword");

        // ── Validation ────────────────────────────────────────────────────────
        if (fullName.isEmpty() || email.isEmpty() || password == null || password.isEmpty()) {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=staff_fields_required");
            return;
        }

        if (!email.matches("^[\\w.+\\-]+@[a-zA-Z0-9.\\-]+\\.[a-zA-Z]{2,}$")) {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=staff_invalid_email");
            return;
        }

        if (password.length() < 8) {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=staff_weak_password");
            return;
        }

        try {
            Connection conn = DBConnection.getConnection();

            // Check for duplicate email
            String checkSQL = "SELECT COUNT(*) FROM users WHERE email = ?";
            try (PreparedStatement ps = conn.prepareStatement(checkSQL)) {
                ps.setString(1, email);
                ResultSet rs = ps.executeQuery();
                if (rs.next() && rs.getInt(1) > 0) {
                    response.sendRedirect(request.getContextPath() + "/admin/action?error=staff_email_exists");
                    return;
                }
            }

            // Insert new Staff record
            String insertSQL = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'Staff')";
            try (PreparedStatement ps = conn.prepareStatement(insertSQL)) {
                ps.setString(1, fullName);
                ps.setString(2, email);
                ps.setString(3, password);
                ps.executeUpdate();
            }

            response.sendRedirect(request.getContextPath() + "/admin/action?success=staff_added");

        } catch (Exception e) {
            e.printStackTrace();
            response.sendRedirect(request.getContextPath() + "/admin/action?error=db_error");
        }
    }

    // ========================================================================
    // DELETE USER — Remove a user from MySQL by their ID
    // ========================================================================
    private void handleDeleteUser(HttpServletRequest request, HttpServletResponse response)
            throws IOException {

        String targetId = sanitize(request.getParameter("targetId"));

        if (targetId.isEmpty()) {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=no_user_id");
            return;
        }

        // Prevent admin from deleting themselves
        HttpSession session = request.getSession(false);
        String adminEmail = (String) session.getAttribute("userEmail");

        try {
            Connection conn = DBConnection.getConnection();

            // Look up the target user's email to prevent self-deletion
            String lookupSQL = "SELECT email FROM users WHERE id = ?";
            try (PreparedStatement ps = conn.prepareStatement(lookupSQL)) {
                ps.setInt(1, Integer.parseInt(targetId));
                ResultSet rs = ps.executeQuery();
                if (rs.next()) {
                    String targetEmail = rs.getString("email");
                    if (targetEmail.equalsIgnoreCase(adminEmail)) {
                        response.sendRedirect(request.getContextPath() + "/admin/action?error=cannot_delete_self");
                        return;
                    }
                }
            }

            // Delete the user (CASCADE will remove their bookings too)
            String deleteSQL = "DELETE FROM users WHERE id = ?";
            try (PreparedStatement ps = conn.prepareStatement(deleteSQL)) {
                ps.setInt(1, Integer.parseInt(targetId));
                int rows = ps.executeUpdate();

                if (rows > 0) {
                    response.sendRedirect(request.getContextPath() + "/admin/action?success=user_deleted");
                } else {
                    response.sendRedirect(request.getContextPath() + "/admin/action?error=user_not_found");
                }
            }

        } catch (Exception e) {
            e.printStackTrace();
            response.sendRedirect(request.getContextPath() + "/admin/action?error=db_error");
        }
    }

    // ========================================================================
    // UPDATE BOOKING STATUS — Change status in MySQL bookings table
    // ========================================================================
    private void handleUpdateStatus(HttpServletRequest request, HttpServletResponse response)
            throws IOException {

        String bookingId = sanitize(request.getParameter("bookingId"));
        String newStatus = sanitize(request.getParameter("newStatus"));

        // Whitelist allowed statuses
        if (!newStatus.equals("Pending") && !newStatus.equals("Confirmed")
                && !newStatus.equals("Cancelled") && !newStatus.equals("Completed")) {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=invalid_status");
            return;
        }

        try {
            Connection conn = DBConnection.getConnection();

            String updateSQL = "UPDATE bookings SET status = ? WHERE id = ?";
            try (PreparedStatement ps = conn.prepareStatement(updateSQL)) {
                ps.setString(1, newStatus);
                ps.setInt(2, Integer.parseInt(bookingId));
                int rows = ps.executeUpdate();

                if (rows > 0) {
                    response.sendRedirect(request.getContextPath() + "/admin/action?success=status_updated");
                } else {
                    response.sendRedirect(request.getContextPath() + "/admin/action?error=booking_not_found");
                }
            }

        } catch (Exception e) {
            e.printStackTrace();
            response.sendRedirect(request.getContextPath() + "/admin/action?error=db_error");
        }
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Executes a COUNT(*) query and returns the integer result.
     */
    private int countQuery(Connection conn, String sql) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            if (rs.next()) return rs.getInt(1);
        }
        return 0;
    }

    /**
     * Trims whitespace from user input. Returns empty string if null.
     */
    private String sanitize(String input) {
        return (input == null) ? "" : input.trim();
    }
}
