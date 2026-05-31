package com.globetrek.servlet;

import com.globetrek.util.DBConnection;
import com.globetrek.util.TemplateRenderer;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * CustomerServlet — Handles Customer Dashboard operations by loading the static
 * customer-dashboard.html template, querying bookings and active packages from MySQL,
 * generating HTML strings via JDBC, and rendering dynamic content directly to the browser.
 */
public class CustomerServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ── Security: verify Customer session ─────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || !"Customer".equals(session.getAttribute("userRole"))) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
            return;
        }

        String email = (String) session.getAttribute("userEmail");
        String firstName = (String) session.getAttribute("userFirstName");
        String lastName = (String) session.getAttribute("userLastName");
        String userStyle = (String) session.getAttribute("userStyle");

        if (firstName == null) firstName = "Traveler";
        if (lastName == null) lastName = "";
        if (userStyle == null) userStyle = "general";

        List<Map<String, String>> bookingList = new ArrayList<>();
        List<Map<String, String>> packageList = new ArrayList<>();
        String dbErrorMsg = null;

        try {
            Connection conn = DBConnection.getConnection();

            // ── 1. Load Customer's Bookings (JOIN bookings and packages) ─────────
            String bookSQL =
                "SELECT b.id, p.name AS package_name, p.destination, p.price, " +
                "       b.status, b.booking_date " +
                "FROM bookings b " +
                "JOIN users u    ON b.user_id    = u.id " +
                "JOIN packages p ON b.package_id = p.id " +
                "WHERE u.email = ? " +
                "ORDER BY b.booking_date DESC";

            try (PreparedStatement ps = conn.prepareStatement(bookSQL)) {
                ps.setString(1, email);
                try (ResultSet rs = ps.executeQuery()) {
                    while (rs.next()) {
                        Map<String, String> booking = new HashMap<>();
                        booking.put("id",            String.valueOf(rs.getInt("id")));
                        booking.put("packageName",   rs.getString("package_name"));
                        booking.put("destination",   rs.getString("destination"));
                        booking.put("price",         String.format("%.2f", rs.getDouble("price")));
                        booking.put("status",        rs.getString("status"));
                        booking.put("bookingDate",   rs.getString("booking_date"));
                        bookingList.add(booking);
                    }
                }
            }

            // ── 2. Load Available Tour Packages from database ────────────────
            String pkgSQL = "SELECT * FROM packages ORDER BY id ASC";
            try (PreparedStatement ps = conn.prepareStatement(pkgSQL);
                 ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Map<String, String> pkg = new HashMap<>();
                    pkg.put("name",        rs.getString("name"));
                    pkg.put("destination", rs.getString("destination"));
                    pkg.put("price",       String.format("%.0f", rs.getDouble("price")));
                    pkg.put("description", rs.getString("description"));
                    packageList.add(pkg);
                }
            }

        } catch (Exception e) {
            e.printStackTrace();
            dbErrorMsg = e.getMessage();
        }

        // ── Load HTML template ──────────────────────────────────────────────
        String html = TemplateRenderer.render(getServletContext(), "/customer-dashboard.html");

        // ── Build Alert Banner ──────────────────────────────────────────────
        String successParam = request.getParameter("success");
        String errorParam   = request.getParameter("error");
        String bookingRefId = request.getParameter("id");
        boolean isWelcome   = "true".equals(request.getParameter("welcome"));

        StringBuilder alertBuilder = new StringBuilder();

        if (isWelcome) {
            alertBuilder.append("<div class=\"dash-alert dash-alert--success\" id=\"welcome-banner\" role=\"alert\">\n")
                        .append("  <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\"/></svg>\n")
                        .append("  Welcome to GlobeTrek! Your account is all set. Start exploring below.\n")
                        .append("</div>\n");
        }

        if ("booked".equals(successParam)) {
            alertBuilder.append("<div class=\"dash-alert dash-alert--success\" id=\"booking-success-banner\" role=\"alert\">\n")
                        .append("  <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\"/></svg>\n")
                        .append("  Booking submitted successfully! Reference: <strong>").append(bookingRefId != null ? bookingRefId : "").append("</strong>. Our team will contact you shortly.\n")
                        .append("</div>\n");
        }

        if (errorParam != null) {
            String msg = "An error occurred. Please try again.";
            if ("missing_fields".equals(errorParam)) {
                msg = "All booking fields are required. Please fill in the form completely.";
            } else if ("invalid_travelers".equals(errorParam)) {
                msg = "Number of travelers must be between 1 and 50.";
            } else if ("db_error".equals(errorParam)) {
                msg = "A database error occurred while creating your booking.";
            }
            alertBuilder.append("<div class=\"dash-alert dash-alert--error\" id=\"booking-error-banner\" role=\"alert\">\n")
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

        // ── Build Dynamic SQL Packages Grid ─────────────────────────────────
        StringBuilder pkgGridBuilder = new StringBuilder();
        pkgGridBuilder.append("<div class=\"pkg-grid\">\n");
        for (Map<String, String> p : packageList) {
            String name = p.get("name");
            String dest = p.get("destination");
            String price = p.get("price");
            String desc = p.get("description");

            String imgUrl = "https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=600&auto=format&fit=crop";
            String duration = "12 Days";

            if (name.toLowerCase().contains("swiss") || dest.toLowerCase().contains("switzerland")) {
                imgUrl = "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&auto=format&fit=crop";
                duration = "10 Days";
            } else if (name.toLowerCase().contains("bali") || dest.toLowerCase().contains("indonesia")) {
                imgUrl = "https://images.unsplash.com/photo-1537953773345-d172ccf13cf4?w=600&auto=format&fit=crop";
                duration = "8 Days";
            } else if (name.toLowerCase().contains("kyoto") || dest.toLowerCase().contains("japan")) {
                imgUrl = "https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=600&auto=format&fit=crop";
                duration = "7 Days";
            }

            pkgGridBuilder.append("  <div class=\"pkg-card\">\n")
                          .append("    <div class=\"pkg-card-img\" style=\"background-image: url('").append(imgUrl).append("');\">\n")
                          .append("      <span class=\"pkg-badge\">").append(duration).append("</span>\n")
                          .append("      <span class=\"pkg-card-img-label\">").append(escapeHtml(name)).append("</span>\n")
                          .append("    </div>\n")
                          .append("    <div class=\"pkg-card-body\">\n")
                          .append("      <div class=\"pkg-card-price\">$").append(price).append(" <span>/ person</span></div>\n")
                          .append("      <div class=\"pkg-card-details\">\n")
                          .append("        <strong>").append(escapeHtml(dest)).append("</strong> &nbsp;·&nbsp; ").append(duration).append("<br>\n")
                          .append("        ").append(escapeHtml(desc)).append("\n")
                          .append("      </div>\n")
                          .append("      <a href=\"#book-section\" class=\"pkg-book-btn\"\n")
                          .append("         onclick=\"\n")
                          .append("           document.getElementById('pkg-name').value='").append(escapeHtml(name)).append("';\n")
                          .append("           document.getElementById('pkg-dest').value='").append(escapeHtml(dest)).append("';\n")
                          .append("         \">\n")
                          .append("        Book This Package\n")
                          .append("      </a>\n")
                          .append("    </div>\n")
                          .append("  </div>\n");
        }
        pkgGridBuilder.append("</div>\n");

        // ── Build Bookings Table ────────────────────────────────────────────
        StringBuilder tableBuilder = new StringBuilder();
        if (bookingList.isEmpty()) {
            tableBuilder.append("<tr>\n")
                        .append("  <td colspan=\"6\" class=\"dash-table-empty\">\n")
                        .append("    <svg viewBox=\"0 0 24 24\"><path d=\"M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z\"/></svg>\n")
                        .append("    No bookings yet. Browse the packages above and submit your first request!\n")
                        .append("  </td>\n")
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

                tableBuilder.append("<tr>\n")
                            .append("  <td><code>").append(ref).append("</code></td>\n")
                            .append("  <td><strong>").append(escapeHtml(b.get("packageName"))).append("</strong></td>\n")
                            .append("  <td>").append(escapeHtml(b.get("destination"))).append("</td>\n")
                            .append("  <td><strong>$").append(b.get("price")).append("</strong></td>\n")
                            .append("  <td><span class=\"badge ").append(statusClass).append("\">").append(status).append("</span></td>\n")
                            .append("  <td><small>").append(b.get("bookingDate")).append("</small></td>\n")
                            .append("</tr>\n");
            }
        }

        // ── Substitute Placeholders ─────────────────────────────────────────
        html = html.replace("{{userFirstName}}", firstName);
        html = html.replace("{{userLastName}}", lastName);
        html = html.replace("{{userEmail}}", email);
        html = html.replace("{{bookingCount}}", String.valueOf(bookingList.size()));
        html = html.replace("<!-- ALERT_BANNER -->", alertBuilder.toString());
        html = html.replace("<!-- PACKAGES_GRID -->", pkgGridBuilder.toString());
        html = html.replace("{{bookingsTable}}", tableBuilder.toString());

        // ── Stream Response ─────────────────────────────────────────────────
        response.setContentType("text/html;charset=UTF-8");
        response.getWriter().write(html);
    }

    private String escapeHtml(String input) {
        if (input == null) return "";
        return input.replace("&", "&amp;")
                    .replace("<", "&lt;")
                    .replace(">", "&gt;")
                    .replace("\"", "&quot;")
                    .replace("'", "&#x27;");
    }
}
