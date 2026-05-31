package com.globetrek.servlet;

import com.globetrek.util.TemplateRenderer;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;

/**
 * IndexServlet — Intercepts requests to the home page (index.html or root /).
 * Loads index.html and dynamically adjusts the header CTA buttons to show "Dashboard"
 * and "Sign Out" if the user is already authenticated.
 */
public class IndexServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        HttpSession session = request.getSession(false);
        String role = (session != null) ? (String) session.getAttribute("userRole") : null;

        String html = TemplateRenderer.render(getServletContext(), "/index.html");

        if (role != null) {
            String dashboardUrl;
            if ("Admin".equals(role)) {
                dashboardUrl = "admin/action";
            } else if ("Staff".equals(role)) {
                dashboardUrl = "staff/action";
            } else {
                dashboardUrl = "customer/dashboard";
            }

            // Replace standard CTA buttons with dynamic session state CTAs
            String standardCta = 
                "            <!-- Call to Actions -->\n" +
                "            <div class=\"nav-cta\" id=\"header-cta-buttons\">\n" +
                "                <a href=\"login.html\" class=\"btn btn-outline\" style=\"padding: 10px 22px; font-size: 0.85rem;\" id=\"cta-login\">Login</a>\n" +
                "                <a href=\"signup.html\" class=\"btn btn-primary\" style=\"padding: 10px 22px; font-size: 0.85rem;\" id=\"cta-signup\">Sign Up</a>\n" +
                "            </div>";

            String loggedInCta = 
                "            <!-- Call to Actions -->\n" +
                "            <div class=\"nav-cta\" id=\"header-cta-buttons\">\n" +
                "                <a href=\"" + dashboardUrl + "\" class=\"btn btn-outline\" style=\"padding: 10px 22px; font-size: 0.85rem; border-color: var(--secondary); color: var(--secondary);\" id=\"cta-dashboard\">Dashboard</a>\n" +
                "                <a href=\"logout\" class=\"btn btn-primary\" style=\"padding: 10px 22px; font-size: 0.85rem;\" id=\"cta-logout\">Sign Out</a>\n" +
                "            </div>";

            // Clean replacement across OS environments by removing carriage returns for normalisation
            html = html.replace("\r", "");
            standardCta = standardCta.replace("\r", "");
            loggedInCta = loggedInCta.replace("\r", "");

            html = html.replace(standardCta, loggedInCta);
        }

        response.setContentType("text/html;charset=UTF-8");
        response.getWriter().write(html);
    }
}
