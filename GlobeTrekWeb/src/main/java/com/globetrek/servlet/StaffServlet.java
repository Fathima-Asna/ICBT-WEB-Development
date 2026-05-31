package com.globetrek.servlet;

import com.globetrek.util.DBConnection;
import com.globetrek.util.TemplateRenderer;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * StaffServlet — Handles package management dashboard views and package updates
 * for GlobeTrek staff members. Uses pure HTML template rendering.
 */
@WebServlet("/staff/action")
public class StaffServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        // ── 1. Security: Verify Staff or Admin session ────────────────────────
        HttpSession session = request.getSession(false);
        String role = (session != null) ? (String) session.getAttribute("userRole") : null;
        if (session == null || (!"Staff".equals(role) && !"Admin".equals(role))) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
            return;
        }

        String firstName = (String) session.getAttribute("userFirstName");
        String lastName = (String) session.getAttribute("userLastName");
        String email = (String) session.getAttribute("userEmail");
        if (firstName == null) firstName = "Staff";
        if (lastName == null) lastName = "";

        List<Map<String, String>> packageList = new ArrayList<>();
        double totalPrice = 0.0;
        double maxPrice = 0.0;
        String dbErrorMsg = null;

        try {
            Connection conn = DBConnection.getConnection();
            String sql = "SELECT * FROM packages ORDER BY id ASC";
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    Map<String, String> pkg = new HashMap<>();
                    pkg.put("id",          String.valueOf(rs.getInt("id")));
                    pkg.put("name",        rs.getString("name"));
                    pkg.put("destination", rs.getString("destination"));
                    pkg.put("price",       String.valueOf(rs.getDouble("price")));
                    pkg.put("description", rs.getString("description"));
                    packageList.add(pkg);

                    double price = rs.getDouble("price");
                    totalPrice += price;
                    if (price > maxPrice) {
                        maxPrice = price;
                    }
                }
            }

        } catch (Exception e) {
            e.printStackTrace();
            dbErrorMsg = e.getMessage();
        }

        double avgPrice = (packageList.isEmpty()) ? 0.0 : (totalPrice / packageList.size());

        // ── Load HTML template ──────────────────────────────────────────────
        String html = TemplateRenderer.render(getServletContext(), "/staff-dashboard.html");

        // ── Build Alert Banner ──────────────────────────────────────────────
        String successParam = request.getParameter("success");
        String errorParam   = request.getParameter("error");

        StringBuilder alertBuilder = new StringBuilder();

        if (successParam != null) {
            String msg = "";
            if ("package_updated".equals(successParam)) {
                msg = "Package description and price updated successfully.";
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
            if ("missing_fields".equals(errorParam)) {
                msg = "All update fields are required. Please check your submission.";
            } else if ("invalid_price".equals(errorParam)) {
                msg = "Price must be a positive number.";
            } else if ("invalid_formats".equals(errorParam)) {
                msg = "Invalid number formatting. Price must be a valid decimal number.";
            } else if ("package_not_found".equals(errorParam)) {
                msg = "Selected package was not found.";
            } else if ("db_error".equals(errorParam)) {
                msg = "Database query failure. Please try again.";
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

        // ── Build Packages Table ────────────────────────────────────────────
        StringBuilder tableBuilder = new StringBuilder();
        if (packageList.isEmpty()) {
            tableBuilder.append("<tr>\n")
                        .append("  <td colspan=\"6\" class=\"dash-table-empty\">\n")
                        .append("    <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z\"/></svg>\n")
                        .append("    No travel packages are currently stored in the database.\n")
                        .append("  </td>\n")
                        .append("</tr>\n");
        } else {
            for (Map<String, String> p : packageList) {
                tableBuilder.append("<tr>\n")
                            .append("  <td><code>GT-").append(p.get("id")).append("</code></td>\n")
                            .append("  <td><strong>").append(escapeHtml(p.get("name"))).append("</strong></td>\n")
                            .append("  <td>").append(escapeHtml(p.get("destination"))).append("</td>\n")
                            .append("  <form action=\"").append(request.getContextPath()).append("/staff/action\" method=\"POST\">\n")
                            .append("    <input type=\"hidden\" name=\"action\" value=\"updatePackage\">\n")
                            .append("    <input type=\"hidden\" name=\"packageId\" value=\"").append(p.get("id")).append("\">\n")
                            .append("    <td>\n")
                            .append("      <input type=\"number\" step=\"0.01\" min=\"0\" name=\"price\" value=\"").append(p.get("price")).append("\" class=\"dash-form-input\" style=\"padding: 6px 10px; font-size: 0.85rem; width: 110px; margin: 0;\" required>\n")
                            .append("    </td>\n")
                            .append("    <td>\n")
                            .append("      <textarea name=\"description\" class=\"dash-form-textarea\" style=\"padding: 6px 10px; font-size: 0.85rem; width: 100%; height: 50px; margin: 0; min-height: 50px;\" required>").append(escapeHtml(p.get("description"))).append("</textarea>\n")
                            .append("    </td>\n")
                            .append("    <td>\n")
                            .append("      <button type=\"submit\" class=\"btn btn-primary btn-sm\" style=\"padding: 8px 16px; font-size: 0.80rem; width: 100%; margin: 0;\">Update</button>\n")
                            .append("    </td>\n")
                            .append("  </form>\n")
                            .append("</tr>\n");
            }
        }

        // ── Render Admin Sidebar Link if applicable ────────────────────────
        String adminLinkHtml = "";
        if ("Admin".equals(role)) {
            adminLinkHtml = "<a href=\"" + request.getContextPath() + "/admin/action\" class=\"sidebar-nav-item\">\n" +
                            "    <svg viewBox=\"0 0 24 24\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\"/></svg>\n" +
                            "    Admin Panel\n" +
                            "</a>";
        }

        // ── Substitute Placeholders ─────────────────────────────────────────
        html = html.replace("{{userRole}}", role);
        html = html.replace("{{userFirstName}}", firstName);
        html = html.replace("{{userLastName}}", lastName);
        html = html.replace("{{userEmail}}", email);
        html = html.replace("{{totalPackages}}", String.valueOf(packageList.size()));
        html = html.replace("{{avgPrice}}", String.format("%.2f", avgPrice));
        html = html.replace("{{maxPrice}}", String.format("%.2f", maxPrice));
        html = html.replace("<!-- ALERT_BANNER -->", alertBuilder.toString());
        html = html.replace("{{packagesTable}}", tableBuilder.toString());
        html = html.replace("<!-- ADMIN_LINK -->", adminLinkHtml);

        // ── Stream Response ─────────────────────────────────────────────────
        response.setContentType("text/html;charset=UTF-8");
        response.getWriter().write(html);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        // ── Security: Verify Staff or Admin session ────────────────────────
        HttpSession session = request.getSession(false);
        String role = (session != null) ? (String) session.getAttribute("userRole") : null;
        if (session == null || (!"Staff".equals(role) && !"Admin".equals(role))) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
            return;
        }

        request.setCharacterEncoding("UTF-8");
        String action = request.getParameter("action");

        if ("updatePackage".equals(action)) {
            String idStrStr = request.getParameter("packageId");
            String priceStr = request.getParameter("price");
            String description = request.getParameter("description");

            if (idStrStr == null || priceStr == null || description == null || description.trim().isEmpty()) {
                response.sendRedirect(request.getContextPath() + "/staff/action?error=missing_fields");
                return;
            }

            try {
                int id = Integer.parseInt(idStrStr);
                double price = Double.parseDouble(priceStr);

                if (price < 0) {
                    response.sendRedirect(request.getContextPath() + "/staff/action?error=invalid_price");
                    return;
                }

                Connection conn = DBConnection.getConnection();
                String sql = "UPDATE packages SET price = ?, description = ? WHERE id = ?";
                try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                    stmt.setDouble(1, price);
                    stmt.setString(2, description.trim());
                    stmt.setInt(3, id);
                    
                    int updatedRows = stmt.executeUpdate();
                    if (updatedRows > 0) {
                        response.sendRedirect(request.getContextPath() + "/staff/action?success=package_updated");
                    } else {
                        response.sendRedirect(request.getContextPath() + "/staff/action?error=package_not_found");
                    }
                }

            } catch (NumberFormatException e) {
                response.sendRedirect(request.getContextPath() + "/staff/action?error=invalid_formats");
            } catch (SQLException | ClassNotFoundException e) {
                e.printStackTrace();
                response.sendRedirect(request.getContextPath() + "/staff/action?error=db_error");
            }
        } else {
            response.sendRedirect(request.getContextPath() + "/staff/action");
        }
    }

    private String escapeHtml(String input) {
        if (input == null) return "";
        return input.replace("&", "&amp;")
                    .replace("<", "&lt;")
                    .replace(">", "&gt;")
                    .replace("\"", "&quot;")
                    .replace("'", "&#x27;");
    }

    private String sanitize(String input) {
        return (input == null) ? "" : input.trim();
    }
}
