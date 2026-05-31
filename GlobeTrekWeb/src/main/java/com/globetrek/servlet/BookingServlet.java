package com.globetrek.servlet;

import com.globetrek.util.DBConnection;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.*;

/**
 * BookingServlet — Processes travel package booking requests submitted by Customers.
 *
 * Mapped to: /book (POST only)
 *
 * Workflow:
 *   1. Reads the logged-in customer's email from the active HttpSession.
 *   2. Validates all booking form fields.
 *   3. Queries MySQL to find the customer's user_id.
 *   4. Queries MySQL to find the package_id (by name or destination).
 *      If not found, inserts the package dynamically to keep data consistent.
 *   5. Inserts the booking record into the bookings table in MySQL.
 *   6. Redirects back to Customer Dashboard with a success parameter.
 */
public class BookingServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");

        // ── Verify session ────────────────────────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("userEmail") == null) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=session_expired");
            return;
        }

        String customerEmail = (String) session.getAttribute("userEmail");

        // ── Collect form parameters ───────────────────────────────────────────
        String packageName   = sanitize(request.getParameter("packageName"));
        String destination   = sanitize(request.getParameter("destination"));
        String travelDate    = sanitize(request.getParameter("travelDate"));
        String travelersStr  = sanitize(request.getParameter("travelers"));
        String specialNotes  = sanitize(request.getParameter("specialNotes"));

        // ── Server-side validation ────────────────────────────────────────────
        if (packageName.isEmpty() || destination.isEmpty() || travelDate.isEmpty() || travelersStr.isEmpty()) {
            response.sendRedirect(request.getContextPath() + "/customer/dashboard?error=missing_fields");
            return;
        }

        int travelers;
        try {
            travelers = Integer.parseInt(travelersStr);
            if (travelers < 1 || travelers > 50) throw new NumberFormatException();
        } catch (NumberFormatException e) {
            response.sendRedirect(request.getContextPath() + "/customer/dashboard?error=invalid_travelers");
            return;
        }

        Connection conn = null;
        PreparedStatement psUser = null;
        PreparedStatement psPkgSelect = null;
        PreparedStatement psPkgInsert = null;
        PreparedStatement psBook = null;
        ResultSet rsUser = null;
        ResultSet rsPkg = null;

        try {
            conn = DBConnection.getConnection();
            conn.setAutoCommit(false); // Transaction

            // ── 1. Find User ID by Email ──────────────────────────────────────
            String userSQL = "SELECT id FROM users WHERE email = ?";
            psUser = conn.prepareStatement(userSQL);
            psUser.setString(1, customerEmail);
            rsUser = psUser.executeQuery();

            if (!rsUser.next()) {
                conn.rollback();
                response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
                return;
            }
            int userId = rsUser.getInt("id");

            // ── 2. Find Package ID (Check Name first, then Destination) ──────
            int packageId = -1;
            String pkgSQL = "SELECT id FROM packages WHERE name = ? OR destination = ?";
            psPkgSelect = conn.prepareStatement(pkgSQL);
            psPkgSelect.setString(1, packageName);
            psPkgSelect.setString(2, destination);
            rsPkg = psPkgSelect.executeQuery();

            if (rsPkg.next()) {
                packageId = rsPkg.getInt("id");
            } else {
                // If the package is not found in the DB (like Patagonia Expedition),
                // insert it dynamically to keep referential integrity!
                String insertPkgSQL = "INSERT INTO packages (name, destination, price, description) VALUES (?, ?, ?, ?)";
                psPkgInsert = conn.prepareStatement(insertPkgSQL, Statement.RETURN_GENERATED_KEYS);
                psPkgInsert.setString(1, packageName);
                psPkgInsert.setString(2, destination);
                // Assign a default reasonable price (e.g. $2999) or based on name
                double price = 2999.00;
                if (packageName.toLowerCase().contains("patagonia")) {
                    price = 4199.00;
                } else if (packageName.toLowerCase().contains("swiss")) {
                    price = 3299.00;
                } else if (packageName.toLowerCase().contains("bali")) {
                    price = 2499.00;
                } else if (packageName.toLowerCase().contains("kyoto")) {
                    price = 2899.00;
                }
                psPkgInsert.setDouble(3, price);
                psPkgInsert.setString(4, specialNotes.isEmpty() ? "Custom travel booking." : specialNotes);
                psPkgInsert.executeUpdate();

                try (ResultSet generatedKeys = psPkgInsert.getGeneratedKeys()) {
                    if (generatedKeys.next()) {
                        packageId = generatedKeys.getInt(1);
                    } else {
                        throw new SQLException("Creating package failed, no ID obtained.");
                    }
                }
            }

            // ── 3. Insert Booking ─────────────────────────────────────────────
            // Note: Since our schema only has (user_id, package_id, status)
            // we will insert these. Booking date is auto-timestamped.
            String bookInsertSQL = "INSERT INTO bookings (user_id, package_id, status) VALUES (?, ?, 'Pending')";
            psBook = conn.prepareStatement(bookInsertSQL, Statement.RETURN_GENERATED_KEYS);
            psBook.setInt(1, userId);
            psBook.setInt(2, packageId);
            psBook.executeUpdate();

            int bookingDbId = -1;
            try (ResultSet generatedKeys = psBook.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    bookingDbId = generatedKeys.getInt(1);
                }
            }

            conn.commit(); // Commit Transaction

            // Format a nice booking reference string (e.g., GT-0104)
            String bookingRef = (bookingDbId != -1) ? String.format("GT-%04d", bookingDbId) : "GT-SUCCESS";

            // ── Redirect with success ─────────────────────────────────────────
            response.sendRedirect(request.getContextPath() + "/customer/dashboard?success=booked&id=" + bookingRef);

        } catch (Exception e) {
            if (conn != null) {
                try { conn.rollback(); } catch (SQLException ignored) {}
            }
            e.printStackTrace();
            response.sendRedirect(request.getContextPath() + "/customer/dashboard?error=db_error");
        } finally {
            try { if (rsUser != null) rsUser.close(); } catch (SQLException ignored) {}
            try { if (rsPkg != null) rsPkg.close(); } catch (SQLException ignored) {}
            try { if (psUser != null) psUser.close(); } catch (SQLException ignored) {}
            try { if (psPkgSelect != null) psPkgSelect.close(); } catch (SQLException ignored) {}
            try { if (psPkgInsert != null) psPkgInsert.close(); } catch (SQLException ignored) {}
            try { if (psBook != null) psBook.close(); } catch (SQLException ignored) {}
            try { if (conn != null) { conn.setAutoCommit(true); conn.close(); } } catch (SQLException ignored) {}
        }
    }

    /**
     * Trims whitespace. Returns empty string for null input.
     */
    private String sanitize(String input) {
        return (input == null) ? "" : input.trim();
    }
}
