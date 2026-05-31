package com.globetrek.servlet;

import com.globetrek.util.TemplateRenderer;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

/**
 * LoginServlet — Intercepts GET requests to /login.html.
 * Renders the static login page and dynamically injects query-based error or success banners
 * (such as session expiration or successful sign out) without JSP or JavaScript.
 */
public class LoginServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String errorParam = request.getParameter("error");
        String logoutParam = request.getParameter("logout");

        String html = TemplateRenderer.render(getServletContext(), "/login.html");
        String bannerHtml = "";

        if (errorParam != null) {
            String msg = "An error occurred. Please try again.";
            if ("unauthorized".equals(errorParam)) {
                msg = "Please log in to access that page.";
            } else if ("forbidden".equals(errorParam)) {
                msg = "You do not have permission to view that page.";
            } else if ("session_expired".equals(errorParam)) {
                msg = "Your session has expired. Please sign in again.";
            }

            bannerHtml = "<div class=\"dash-alert dash-alert--error\" style=\"margin-bottom: 20px; display: flex; align-items: center; gap: 10px; background: rgba(235, 94, 85, 0.1); border-left: 4px solid var(--primary); padding: 12px 18px; border-radius: 8px; color: var(--primary); font-size: 0.9rem;\">\n" +
                         "  <svg viewBox=\"0 0 24 24\" style=\"width: 20px; height: 20px; fill: var(--primary); flex-shrink: 0;\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z\"/></svg>\n" +
                         "  <span>" + msg + "</span>\n" +
                         "</div>";
        } else if ("true".equals(logoutParam)) {
            bannerHtml = "<div class=\"dash-alert dash-alert--success\" style=\"margin-bottom: 20px; display: flex; align-items: center; gap: 10px; background: rgba(13, 115, 119, 0.1); border-left: 4px solid var(--secondary); padding: 12px 18px; border-radius: 8px; color: var(--secondary); font-size: 0.9rem;\">\n" +
                         "  <svg viewBox=\"0 0 24 24\" style=\"width: 20px; height: 20px; fill: var(--secondary); flex-shrink: 0;\"><path d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z\"/></svg>\n" +
                         "  <span>You have been logged out successfully.</span>\n" +
                         "</div>";
        }

        html = html.replace("<!-- ERROR_BANNER -->", bannerHtml);

        response.setContentType("text/html;charset=UTF-8");
        response.getWriter().write(html);
    }
}
