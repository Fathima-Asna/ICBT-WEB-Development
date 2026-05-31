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
 * AuthServlet — Handles all authentication operations for GlobeTrek Adventures.
 *
 * Mapped to: /auth
 *
 * POST Actions (distinguished by hidden 'action' field in the HTML form):
 *   action=login  → Validate credentials against MySQL users table, create HttpSession,
 *                   redirect to role-specific dashboard.
 *   action=signup → Register new Customer in MySQL users table, auto-login.
 *
 * Data storage: MySQL globetrek_db
 *
 * NOTE (Academic): Passwords are stored in plaintext for simplicity as required
 * by the academic spec. In a production system, always hash passwords.
 */
public class AuthServlet extends HttpServlet {

    // ── Roles ────────────────────────────────────────────────────────────────
    public static final String ROLE_ADMIN    = "Admin";
    public static final String ROLE_STAFF    = "Staff";
    public static final String ROLE_CUSTOMER = "Customer";

    // ── Dashboard redirect paths ─────────────────────────────────────────────
    private static final String ADMIN_DASH    = "admin-dashboard.jsp";
    private static final String STAFF_DASH    = "staff/action";
    private static final String CUSTOMER_DASH = "customer-dashboard.jsp";

    // ── Route POST requests ──────────────────────────────────────────────────
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        String action = request.getParameter("action");

        if ("login".equals(action)) {
            handleLogin(request, response);
        } else if ("signup".equals(action)) {
            handleSignup(request, response);
        } else {
            // Unknown action — redirect home
            response.sendRedirect(request.getContextPath() + "/index.jsp");
        }
    }

    // ============================================================
    // LOGIN HANDLER
    // ============================================================
    private void handleLogin(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String email    = sanitize(request.getParameter("email"));
        String password = request.getParameter("password");

        // Basic null guard
        if (email == null || email.isEmpty() || password == null || password.isEmpty()) {
            forwardToLogin(request, response, "Email and password are required.");
            return;
        }

        Connection conn = null;
        PreparedStatement ps = null;
        ResultSet rs = null;

        try {
            conn = DBConnection.getConnection();
            String sql = "SELECT * FROM users WHERE email = ?";
            ps = conn.prepareStatement(sql);
            ps.setString(1, email);
            rs = ps.executeQuery();

            if (!rs.next()) {
                // No user found with this email
                forwardToLogin(request, response, "No account found with that email address.");
                return;
            }

            String dbPassword = rs.getString("password");
            if (!dbPassword.equals(password)) {
                // Wrong password
                forwardToLogin(request, response, "Incorrect password. Please try again.");
                return;
            }

            // ── Credentials valid — create session ───────────────────────────────
            String role = rs.getString("role");
            String fullName = rs.getString("full_name");
            
            // Split full name for session attributes consistency
            String firstName = "Traveler";
            String lastName = "";
            if (fullName != null && !fullName.trim().isEmpty()) {
                String[] nameParts = fullName.trim().split("\\s+", 2);
                firstName = nameParts[0];
                if (nameParts.length > 1) {
                    lastName = nameParts[1];
                }
            }

            createUserSession(request, email, role, firstName, lastName, fullName, "general");

            // ── Redirect to role-appropriate dashboard ───────────────────────────
            String redirectUrl = request.getContextPath() + "/";

            switch (role) {
                case ROLE_ADMIN:    redirectUrl += ADMIN_DASH;    break;
                case ROLE_STAFF:    redirectUrl += STAFF_DASH;    break;
                case ROLE_CUSTOMER: redirectUrl += CUSTOMER_DASH; break;
                default:            redirectUrl += "index.jsp";   break;
            }

            response.sendRedirect(redirectUrl);

        } catch (SQLException | ClassNotFoundException e) {
            e.printStackTrace();
            forwardToLogin(request, response, "Database error: " + e.getMessage());
        } finally {
            try { if (rs != null) rs.close(); } catch (SQLException ignored) {}
            try { if (ps != null) ps.close(); } catch (SQLException ignored) {}
        }
    }

    // ============================================================
    // SIGNUP HANDLER
    // ============================================================
    private void handleSignup(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Collect form parameters
        String firstName    = sanitize(request.getParameter("first_name"));
        String lastName     = sanitize(request.getParameter("last_name"));
        String email        = sanitize(request.getParameter("email"));
        String password     = request.getParameter("password");
        String confirmPass  = request.getParameter("confirm_password");
        String travelStyle  = sanitize(request.getParameter("travel_style"));

        // ── Server-side validation ────────────────────────────────────────────

        if (firstName == null || firstName.isEmpty() ||
            lastName  == null || lastName.isEmpty()  ||
            email     == null || email.isEmpty()      ||
            password  == null || password.isEmpty()) {
            forwardToSignup(request, response, "All fields are required.", firstName, lastName, email, travelStyle);
            return;
        }

        if (!email.matches("^[\\w.+\\-]+@[a-zA-Z0-9.\\-]+\\.[a-zA-Z]{2,}$")) {
            forwardToSignup(request, response, "Please enter a valid email address.", firstName, lastName, email, travelStyle);
            return;
        }

        if (password.length() < 8) {
            forwardToSignup(request, response, "Password must be at least 8 characters.", firstName, lastName, email, travelStyle);
            return;
        }

        if (!password.equals(confirmPass)) {
            forwardToSignup(request, response, "Passwords do not match. Please re-enter.", firstName, lastName, email, travelStyle);
            return;
        }

        Connection conn = null;
        PreparedStatement checkPs = null;
        PreparedStatement insertPs = null;
        ResultSet checkRs = null;

        try {
            conn = DBConnection.getConnection();

            // Check for duplicate email
            String checkSql = "SELECT COUNT(*) FROM users WHERE email = ?";
            checkPs = conn.prepareStatement(checkSql);
            checkPs.setString(1, email);
            checkRs = checkPs.executeQuery();

            if (checkRs.next() && checkRs.getInt(1) > 0) {
                forwardToSignup(request, response, "An account with this email already exists. Please log in.", firstName, lastName, email, travelStyle);
                return;
            }

            // Combine names into full_name
            String fullName = firstName + " " + lastName;

            // ── Insert new user to MySQL ──────────────────────────────────────────
            String insertSql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'Customer')";
            insertPs = conn.prepareStatement(insertSql);
            insertPs.setString(1, fullName);
            insertPs.setString(2, email);
            insertPs.setString(3, password);
            insertPs.executeUpdate();

            // ── Auto-login: build a session for the new user ──────────────────────
            createUserSession(request, email, ROLE_CUSTOMER, firstName, lastName, fullName, (travelStyle != null && !travelStyle.isEmpty() ? travelStyle : "general"));

            // ── Redirect to customer dashboard ────────────────────────────────────
            response.sendRedirect(request.getContextPath() + "/" + CUSTOMER_DASH + "?welcome=true");

        } catch (SQLException | ClassNotFoundException e) {
            e.printStackTrace();
            forwardToSignup(request, response, "Database error: " + e.getMessage(), firstName, lastName, email, travelStyle);
        } finally {
            try { if (checkRs != null) checkRs.close(); } catch (SQLException ignored) {}
            try { if (checkPs != null) checkPs.close(); } catch (SQLException ignored) {}
            try { if (insertPs != null) insertPs.close(); } catch (SQLException ignored) {}
        }
    }

    // ============================================================
    // SESSION FACTORY
    // ============================================================
    /**
     * Creates (or replaces) an HttpSession with the authenticated user's data.
     * Invalidating any previous session prevents session fixation attacks.
     */
    private void createUserSession(HttpServletRequest request, String email, String role, 
                                   String firstName, String lastName, String fullName, String travelStyle) {
        HttpSession existing = request.getSession(false);
        if (existing != null) {
            existing.invalidate(); // clear old session
        }
        HttpSession session = request.getSession(true);
        session.setAttribute("userEmail",      email);
        session.setAttribute("userRole",       role);
        session.setAttribute("userFirstName",  firstName);
        session.setAttribute("userLastName",   lastName);
        session.setAttribute("userName",       fullName);
        session.setAttribute("userStyle",      travelStyle != null ? travelStyle : "general");
        session.setMaxInactiveInterval(30 * 60); // 30 minutes
    }

    // ============================================================
    // FORWARD HELPERS
    // ============================================================

    /**
     * Repopulates the login page with an error message via request attributes.
     * The error is displayed using JSP Expression Language in login.jsp.
     */
    private void forwardToLogin(HttpServletRequest request, HttpServletResponse response,
                                String errorMsg) throws ServletException, IOException {
        request.setAttribute("errorMsg", errorMsg);
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }

    /**
     * Repopulates the signup page with an error message AND the field values
     * the user typed so they don't have to re-enter everything.
     */
    private void forwardToSignup(HttpServletRequest request, HttpServletResponse response,
                                 String errorMsg, String firstName, String lastName,
                                 String email, String travelStyle)
            throws ServletException, IOException {
        request.setAttribute("errorMsg",    errorMsg);
        request.setAttribute("firstName",   firstName);
        request.setAttribute("lastName",    lastName);
        request.setAttribute("email",       email);
        request.setAttribute("travelStyle", travelStyle);
        request.getRequestDispatcher("/signup.jsp").forward(request, response);
    }

    // ============================================================
    // INPUT SANITIZATION
    // ============================================================

    /**
     * Trims whitespace from user input. Returns empty string if null.
     */
    private String sanitize(String input) {
        return input == null ? "" : input.trim();
    }
}
