package com.globetrek.filter;

import javax.servlet.*;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;

/**
 * AuthFilter — Intercepts requests to protected resources and enforces
 * role-based access control using the active HttpSession.
 *
 * Protected URL patterns (declared in web.xml):
 *   /customer-dashboard.html  → requires role: Customer (or higher)
 *   /staff-dashboard.html     → requires role: Staff or Admin
 *   /admin-dashboard.html     → requires role: Admin only
 *   /admin/action            → requires role: Admin only
 *   /book                    → requires any authenticated session
 *
 * If no valid session exists → redirect to /login.html?error=unauthorized
 * If session exists but role is insufficient → redirect to their own dashboard
 */
public class AuthFilter implements Filter {

    @Override
    public void init(FilterConfig filterConfig) throws ServletException {
        // No initialisation needed
    }

    @Override
    public void doFilter(ServletRequest servletRequest,
                         ServletResponse servletResponse,
                         FilterChain chain)
            throws IOException, ServletException {

        HttpServletRequest  request  = (HttpServletRequest)  servletRequest;
        HttpServletResponse response = (HttpServletResponse) servletResponse;

        // ── 1. Retrieve session (do NOT create a new one) ────────────────────
        HttpSession session = request.getSession(false);
        String role = (session != null) ? (String) session.getAttribute("userRole") : null;

        // ── 2. No authenticated session → force login ─────────────────────────
        if (role == null) {
            response.sendRedirect(request.getContextPath() + "/login.html?error=unauthorized");
            return;
        }

        // ── 3. Determine which resource is being requested ────────────────────
        String requestURI = request.getRequestURI();
        String contextPath = request.getContextPath();

        // Strip context path to get the resource path
        String resourcePath = requestURI.startsWith(contextPath)
                ? requestURI.substring(contextPath.length())
                : requestURI;

        // ── 4. Admin-only resources ───────────────────────────────────────────
        if (resourcePath.startsWith("/admin-dashboard.html") ||
            resourcePath.startsWith("/admin/action")) {

            if (!"Admin".equals(role)) {
                // Redirect non-admins to their own dashboard
                response.sendRedirect(request.getContextPath() + getDashboardForRole(role));
                return;
            }
        }

        // ── 5. Staff or Admin resources ───────────────────────────────────────
        if (resourcePath.startsWith("/staff-dashboard.html") ||
            resourcePath.startsWith("/staff/action")) {
            if (!"Staff".equals(role) && !"Admin".equals(role)) {
                response.sendRedirect(request.getContextPath() + getDashboardForRole(role));
                return;
            }
        }

        // ── 6. Customer-only resources (booking, customer dashboard) ──────────
        if (resourcePath.startsWith("/customer-dashboard.html") ||
            resourcePath.startsWith("/customer/dashboard")) {
            if (!"Customer".equals(role)) {
                response.sendRedirect(request.getContextPath() + getDashboardForRole(role));
                return;
            }
        }

        // ── 7. All checks passed — proceed ────────────────────────────────────
        chain.doFilter(request, response);
    }

    /**
     * Maps a role string to the matching dashboard JSP path.
     */
    private String getDashboardForRole(String role) {
        if (role == null) return "/login.html";
        switch (role) {
            case "Admin":    return "/admin/action";
            case "Staff":    return "/staff/action";
            case "Customer": return "/customer/dashboard";
            default:         return "/login.html";
        }
    }

    @Override
    public void destroy() {
        // Cleanup if needed
    }
}
