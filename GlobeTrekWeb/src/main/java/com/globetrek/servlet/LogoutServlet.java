package com.globetrek.servlet;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;

/**
 * LogoutServlet — Invalidates the active user session and redirects to login.
 *
 * Mapped to: /logout (GET)
 *
 * Triggered by the "Log Out" button on all dashboard pages.
 * Uses GET (not POST) because logout is a navigation action, not a data mutation.
 */
public class LogoutServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Retrieve existing session — do NOT create a new one
        HttpSession session = request.getSession(false);

        if (session != null) {
            // Invalidate destroys all session attributes and the session itself
            session.invalidate();
        }

        // Redirect to login page with a logout success indicator
        response.sendRedirect(request.getContextPath() + "/login.jsp?logout=true");
    }

    /**
     * Also handle POST logout to support form-based logout buttons.
     */
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doGet(request, response);
    }
}
