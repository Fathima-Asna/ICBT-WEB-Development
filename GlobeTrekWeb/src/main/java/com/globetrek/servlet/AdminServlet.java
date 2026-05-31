package com.globetrek.servlet;

import com.globetrek.util.DBConnection;
import com.globetrek.util.TemplateRenderer;

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
 * AdminServlet — Handles Admin Dashboard operations.
 * Fetches dashboard statistics, all user records, and booking lists from MySQL.
 * Renders pure HTML to the browser by dynamically substituting placeholders in admin-dashboard.html.
 */
public class AdminServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ── Security: verify Admin session ────────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || !"Admin".equals(session.getAttribute("userRole"))) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
            return;
        }

        String adminName = (String) session.getAttribute("userName");
        String adminEmail = (String) session.getAttribute("userEmail");
        if (adminName == null) adminName = "Administrator";

        int totalUsers = 0;
        int totalBookings = 0;
        int staffCount = 0;
        int customerCount = 0;

        List<Map<String, String>> userList = new ArrayList<>();
        List<Map<String, String>> bookingList = new ArrayList<>();
        String dbErrorMsg = null;

        try {
            Connection conn = DBConnection.getConnection();

            // ── 1. Dashboard Metrics ──────────────────────────────────────────
            totalUsers    = countQuery(conn, "SELECT COUNT(*) FROM users");
            totalBookings = countQuery(conn, "SELECT COUNT(*) FROM bookings");
            staffCount    = countQuery(conn, "SELECT COUNT(*) FROM users WHERE role = 'Staff'");
            customerCount = countQuery(conn, "SELECT COUNT(*) FROM users WHERE role = 'Customer'");

            // ── 2. All Users ──────────────────────────────────────────────────
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

            // ── 3. All Bookings (JOIN with users and packages) ────────────────
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

        } catch (Exception e) {
            e.printStackTrace();
            dbErrorMsg = e.getMessage();
        }

        // ── Load HTML template ──────────────────────────────────────────────
        String html = TemplateRenderer.render(getServletContext(), "/admin-dashboard.html");

        // ── Build Alert Banner ──────────────────────────────────────────────
        String successParam = request.getParameter("success");
        String errorParam   = request.getParameter("error");

        StringBuilder alertBuilder = new StringBuilder();

        if (successParam != null) {
            String msg = "";
            if ("staff_added".equals(successParam)) {
                msg = "New staff member added successfully.";
            } else if ("user_deleted".equals(successParam)) {
                msg = "User account deleted successfully.";
            } else if ("status_updated".equals(successParam)) {
                msg = "Booking status updated successfully.";
            }

            if (!msg.isEmpty()) {
                alertBuilder.append("<div class=\"dash-alert dash-alert--success\" role=\"alert\">\n")
                            .append("  <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\"/></svg>\n")
                            .append("  ").append(msg).append("\n")
                            .append("</div>\n");
            }
        }

        if (errorParam != null) {
            String msg = "An unexpected error occurred.";
            if ("staff_fields_required".equals(errorParam)) {
                msg = "All staff fields are required.";
            } else if ("staff_invalid_email".equals(errorParam)) {
                msg = "Invalid email address for staff account.";
            } else if ("staff_weak_password".equals(errorParam)) {
                msg = "Staff password must be at least 8 characters.";
            } else if ("staff_email_exists".equals(errorParam)) {
                msg = "An account with this email already exists.";
            } else if ("cannot_delete_self".equals(errorParam)) {
                msg = "You cannot delete your own admin account.";
            } else if ("user_not_found".equals(errorParam)) {
                msg = "User not found in the system.";
            } else if ("booking_not_found".equals(errorParam)) {
                msg = "Booking ID not found.";
            } else if ("invalid_status".equals(errorParam)) {
                msg = "Invalid booking status value.";
            } else if ("db_error".equals(errorParam)) {
                msg = "A database error occurred. Please try again.";
            }

            alertBuilder.append("<div class=\"dash-alert dash-alert--error\" role=\"alert\">\n")
                        .append("  <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z\"/></svg>\n")
                        .append("  ").append(msg).append("\n")
                        .append("</div>\n");
        }

        if (dbErrorMsg != null) {
            alertBuilder.append("<div class=\"dash-alert dash-alert--error\" role=\"alert\">\n")
                        .append("  <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z\"/></svg>\n")
                        .append("  Database error: ").append(dbErrorMsg).append("\n")
                        .append("</div>\n");
        }

        // ── Build Users Table ───────────────────────────────────────────────
        StringBuilder usersBuilder = new StringBuilder();
        if (userList.isEmpty()) {
            usersBuilder.append("<tr>\n")
                        .append("  <td colspan=\"6\" class=\"dash-table-empty\">No user accounts recorded yet.</td>\n")
                        .append("</tr>\n");
        } else {
            for (Map<String, String> u : userList) {
                usersBuilder.append("<tr>\n")
                            .append("  <td><code>").append(u.get("id")).append("</code></td>\n")
                            .append("  <td><strong>").append(u.get("fullName")).append("</strong></td>\n")
                            .append("  <td>").append(u.get("email")).append("</td>\n")
                            .append("  <td><span class=\"badge role-").append(u.get("role").toLowerCase()).append("\">").append(u.get("role")).append("</span></td>\n")
                            .append("  <td><small>").append(u.get("createdAt")).append("</small></td>\n")
                            .append("  <td>\n")
                            .append("    <form action=\"").append(request.getContextPath()).append("/admin/action\" method=\"POST\" onsubmit=\"return confirm('Are you sure you want to permanently delete this user account?');\" style=\"display:inline;\">\n")
                            .append("      <input type=\"hidden\" name=\"action\" value=\"deleteUser\">\n")
                            .append("      <input type=\"hidden\" name=\"targetId\" value=\"").append(u.get("id")).append("\">\n")
                            .append("      <button type=\"submit\" class=\"btn-table-action btn-delete\" style=\"background:#eb5e55; color:#fff; border-color:#eb5e55;\">Delete</button>\n")
                            .append("    </form>\n")
                            .append("  </td>\n")
                            .append("</tr>\n");
            }
        }

        // ── Build Bookings Table ────────────────────────────────────────────
        StringBuilder bookingsBuilder = new StringBuilder();
        if (bookingList.isEmpty()) {
            bookingsBuilder.append("<tr>\n")
                           .append("  <td colspan=\"8\" class=\"dash-table-empty\">No bookings recorded yet.</td>\n")
                           .append("</tr>\n");
        } else {
            for (Map<String, String> b : bookingList) {
                int dbId = Integer.parseInt(b.get("id"));
                String ref = String.format("GT-%04d", dbId);
                String status = b.get("status");

                String statusClass = "badge-pending";
                if ("Confirmed".equals(status)) statusClass = "badge-confirmed";
                else if ("Cancelled".equals(status)) statusClass = "badge-cancelled";
                else if ("Completed".equals(status)) statusClass = "badge-completed";

                bookingsBuilder.append("<tr>\n")
                               .append("  <td><code>").append(ref).append("</code></td>\n")
                               .append("  <td>\n")
                               .append("    <strong>").append(b.get("customerName")).append("</strong>\n")
                               .append("    <small style=\"display:block; color:#6B8E92;\">").append(b.get("customerEmail")).append("</small>\n")
                               .append("  </td>\n")
                               .append("  <td><strong>").append(b.get("packageName")).append("</strong></td>\n")
                               .append("  <td>").append(b.get("destination")).append("</td>\n")
                               .append("  <td><strong>$").append(b.get("price")).append("</strong></td>\n")
                               .append("  <td><span class=\"badge ").append(statusClass).append("\">").append(status).append("</span></td>\n")
                               .append("  <td><small>").append(b.get("bookingDate")).append("</small></td>\n")
                               .append("  <td>\n")
                               .append("    <form action=\"").append(request.getContextPath()).append("/admin/action\" method=\"POST\" style=\"display:inline-flex;gap:4px;align-items:center;\">\n")
                               .append("      <input type=\"hidden\" name=\"action\"    value=\"updateStatus\">\n")
                               .append("      <input type=\"hidden\" name=\"bookingId\" value=\"").append(b.get("id")).append("\">\n")
                               .append("      <select name=\"newStatus\" class=\"dash-form-select\" style=\"padding:4px 8px;font-size:0.75rem;border-radius:6px;\">\n")
                               .append("        <option value=\"Pending\"   ").append("Pending".equals(status) ? "selected" : "").append(">Pending</option>\n")
                               .append("        <option value=\"Confirmed\" ").append("Confirmed".equals(status) ? "selected" : "").append(">Confirmed</option>\n")
                               .append("        <option value=\"Cancelled\" ").append("Cancelled".equals(status) ? "selected" : "").append(">Cancelled</option>\n")
                               .append("        <option value=\"Completed\" ").append("Completed".equals(status) ? "selected" : "").append(">Completed</option>\n")
                               .append("      </select>\n")
                               .append("      <button type=\"submit\" class=\"btn-table-action\" style=\"background:#0D7377;color:#fff;border-color:#0D7377;\">Save</button>\n")
                               .append("    </form>\n")
                               .append("  </td>\n")
                               .append("</tr>\n");
            }
        }

        // ── Substitute Placeholders ─────────────────────────────────────────
        html = html.replace("{{adminName}}", adminName);
        html = html.replace("{{adminEmail}}", adminEmail);
        html = html.replace("{{totalUsers}}", String.valueOf(totalUsers));
        html = html.replace("{{totalBookings}}", String.valueOf(totalBookings));
        html = html.replace("{{staffCount}}", String.valueOf(staffCount));
        html = html.replace("{{customerCount}}", String.valueOf(customerCount));
        html = html.replace("<!-- ALERT_BANNER -->", alertBuilder.toString());
        html = html.replace("{{usersTable}}", usersBuilder.toString());
        html = html.replace("{{bookingsTable}}", bookingsBuilder.toString());

        // ── Stream Response ─────────────────────────────────────────────────
        response.setContentType("text/html;charset=UTF-8");
        response.getWriter().write(html);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");

        // ── Security: verify Admin session ────────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || !"Admin".equals(session.getAttribute("userRole"))) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
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

    private void handleAddStaff(HttpServletRequest request, HttpServletResponse response)
            throws IOException {

        String fullName = sanitize(request.getParameter("staffFullName"));
        String email    = sanitize(request.getParameter("staffEmail"));
        String password = request.getParameter("staffPassword");

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

    private void handleDeleteUser(HttpServletRequest request, HttpServletResponse response)
            throws IOException {

        String targetId = sanitize(request.getParameter("targetId"));

        if (targetId.isEmpty()) {
            response.sendRedirect(request.getContextPath() + "/admin/action?error=no_user_id");
            return;
        }

        HttpSession session = request.getSession(false);
        String adminEmail = (String) session.getAttribute("userEmail");

        try {
            Connection conn = DBConnection.getConnection();

            // Check for self-deletion
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

            // Delete user
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

    private void handleUpdateStatus(HttpServletRequest request, HttpServletResponse response)
            throws IOException {

        String bookingId = sanitize(request.getParameter("bookingId"));
        String newStatus = sanitize(request.getParameter("newStatus"));

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

    private int countQuery(Connection conn, String sql) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            if (rs.next()) return rs.getInt(1);
        }
        return 0;
    }

    private String sanitize(String input) {
        return (input == null) ? "" : input.trim();
    }
}
