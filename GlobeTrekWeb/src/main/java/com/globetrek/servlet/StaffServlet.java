package com.globetrek.servlet;

import com.globetrek.util.DBConnection;

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
 * for GlobeTrek staff members.
 *
 * Mapped to: /staff/action (Session validation enforced on GET and POST)
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
            response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
            return;
        }

        List<Map<String, Object>> packageList = new ArrayList<>();
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;

        try {
            conn = DBConnection.getConnection();
            String sql = "SELECT * FROM packages ORDER BY id ASC";
            stmt = conn.prepareStatement(sql);
            rs = stmt.executeQuery();

            while (rs.next()) {
                Map<String, Object> pkg = new HashMap<>();
                pkg.put("id", rs.getInt("id"));
                pkg.put("name", rs.getString("name"));
                pkg.put("destination", rs.getString("destination"));
                pkg.put("price", rs.getDouble("price"));
                pkg.put("description", rs.getString("description"));
                packageList.add(pkg);
            }

            // Bind list to request and forward to stays dashboard
            request.setAttribute("packageList", packageList);
            request.getRequestDispatcher("/staff-dashboard.jsp").forward(request, response);

        } catch (SQLException | ClassNotFoundException e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Database error loading packages");
        } finally {
            try { if (rs != null) rs.close(); } catch (SQLException ignored) {}
            try { if (stmt != null) stmt.close(); } catch (SQLException ignored) {}
        }
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        // ── 1. Security: Verify Staff or Admin session ────────────────────────
        HttpSession session = request.getSession(false);
        String role = (session != null) ? (String) session.getAttribute("userRole") : null;
        if (session == null || (!"Staff".equals(role) && !"Admin".equals(role))) {
            response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
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

            Connection conn = null;
            PreparedStatement stmt = null;

            try {
                int id = Integer.parseInt(idStrStr);
                double price = Double.parseDouble(priceStr);

                if (price < 0) {
                    response.sendRedirect(request.getContextPath() + "/staff/action?error=invalid_price");
                    return;
                }

                conn = DBConnection.getConnection();
                String sql = "UPDATE packages SET price = ?, description = ? WHERE id = ?";
                stmt = conn.prepareStatement(sql);
                stmt.setDouble(1, price);
                stmt.setString(2, description.trim());
                stmt.setInt(3, id);
                
                int updatedRows = stmt.executeUpdate();
                if (updatedRows > 0) {
                    response.sendRedirect(request.getContextPath() + "/staff/action?success=package_updated");
                } else {
                    response.sendRedirect(request.getContextPath() + "/staff/action?error=package_not_found");
                }

            } catch (NumberFormatException e) {
                response.sendRedirect(request.getContextPath() + "/staff/action?error=invalid_formats");
            } catch (SQLException | ClassNotFoundException e) {
                e.printStackTrace();
                response.sendRedirect(request.getContextPath() + "/staff/action?error=db_error");
            } finally {
                try { if (stmt != null) stmt.close(); } catch (SQLException ignored) {}
            }
        } else {
            response.sendRedirect(request.getContextPath() + "/staff/action");
        }
    }
}
