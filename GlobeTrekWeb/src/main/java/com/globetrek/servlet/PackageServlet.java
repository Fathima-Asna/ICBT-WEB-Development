package com.globetrek.servlet;

import com.globetrek.util.DBConnection;
import com.globetrek.util.TemplateRenderer;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * PackageServlet — Handles searching/filtering travel packages by destination
 * and renders pure dynamic HTML by reading packages.html and substituting SQL results.
 */
@WebServlet("/packages")
public class PackageServlet extends HttpServlet {
    
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
            
        String searchDestination = request.getParameter("destination");
        List<Map<String, String>> packageList = new ArrayList<>();
        
        // Base query vs Search query
        String sql = (searchDestination != null && !searchDestination.trim().isEmpty()) 
                     ? "SELECT * FROM packages WHERE destination LIKE ?" 
                     : "SELECT * FROM packages";
                     
        try {
            Connection conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                if (searchDestination != null && !searchDestination.trim().isEmpty()) {
                    stmt.setString(1, "%" + searchDestination.trim() + "%");
                }
                
                try (ResultSet rs = stmt.executeQuery()) {
                    while (rs.next()) {
                        Map<String, String> pkg = new HashMap<>();
                        pkg.put("id",          String.valueOf(rs.getInt("id")));
                        pkg.put("name",        rs.getString("name"));
                        pkg.put("destination", rs.getString("destination"));
                        pkg.put("price",       String.format("%.2f", rs.getDouble("price")));
                        pkg.put("description", rs.getString("description"));
                        packageList.add(pkg);
                    }
                }
            }
            
        } catch (Exception e) {
            e.printStackTrace();
        }

        // ── Load HTML template ──────────────────────────────────────────────
        String html = TemplateRenderer.render(getServletContext(), "/packages.html");

        // ── Build Package Cards HTML ────────────────────────────────────────
        StringBuilder cardsBuilder = new StringBuilder();
        if (packageList.isEmpty()) {
            cardsBuilder.append("<div style=\"text-align: center; width: 100%; grid-column: 1 / -1; padding: 40px; color: #6b8e92;\">\n")
                        .append("    <h3>No packages found matching your search.</h3>\n")
                        .append("    <p>Try searching for \"Switzerland\", \"Indonesia\", \"Japan\", or clear the search to view all.</p>\n")
                        .append("</div>\n");
        } else {
            for (Map<String, String> p : packageList) {
                String name = p.get("name");
                String dest = p.get("destination");
                String price = p.get("price");
                String desc = p.get("description");
                String id = p.get("id");

                // Determine category and image
                String category = "eco";
                String imgUrl = "https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=600&auto=format&fit=crop";
                String duration = "12 Days";
                String badge = "Eco Trek";

                if (name.toLowerCase().contains("swiss") || dest.toLowerCase().contains("switzerland")) {
                    category = "alpine";
                    imgUrl = "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&auto=format&fit=crop";
                    duration = "10 Days";
                    badge = "Alpine";
                } else if (name.toLowerCase().contains("bali") || dest.toLowerCase().contains("indonesia")) {
                    category = "tropical";
                    imgUrl = "https://images.unsplash.com/photo-1537953773345-d172ccf13cf4?w=600&auto=format&fit=crop";
                    duration = "8 Days";
                    badge = "Tropical";
                } else if (name.toLowerCase().contains("kyoto") || dest.toLowerCase().contains("japan")) {
                    category = "cultural";
                    imgUrl = "https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=600&auto=format&fit=crop";
                    duration = "7 Days";
                    badge = "Cultural";
                }

                cardsBuilder.append("<article class=\"package-card ").append(category).append("\" id=\"package-").append(id).append("\">\n")
                            .append("    <div class=\"package-media\">\n")
                            .append("        <img class=\"package-img\" src=\"").append(imgUrl).append("\" alt=\"").append(escapeHtml(name)).append("\">\n")
                            .append("        <span class=\"package-badge\">").append(badge).append("</span>\n")
                            .append("        <div class=\"package-meta\">\n")
                            .append("            <span class=\"package-duration\">\n")
                            .append("                <svg viewBox=\"0 0 24 24\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z\"/></svg>\n")
                            .append("                ").append(duration).append("\n")
                            .append("            </span>\n")
                            .append("            <span class=\"package-rating\">\n")
                            .append("                <svg viewBox=\"0 0 24 24\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z\"/></svg>\n")
                            .append("                4.9 (124 reviews)\n")
                            .append("            </span>\n")
                            .append("        </div>\n")
                            .append("    </div>\n")
                            .append("    <div class=\"package-info\">\n")
                            .append("        <h3>").append(escapeHtml(name)).append("</h3>\n")
                            .append("        <p>").append(escapeHtml(desc)).append("</p>\n")
                            .append("        <div class=\"package-footer\">\n")
                            .append("            <div class=\"package-price\">\n")
                            .append("                <span>Price per person</span>\n")
                            .append("                <span>$").append(price).append("</span>\n")
                            .append("            </div>\n")
                            .append("            <a href=\"contact.html\" class=\"btn btn-primary btn-sm\" style=\"padding: 10px 20px; font-size: 0.85rem;\" id=\"book-").append(id).append("\">Book Tour</a>\n")
                            .append("        </div>\n")
                            .append("    </div>\n")
                            .append("</article>\n");
            }
        }

        // ── Substitute Placeholders ─────────────────────────────────────────
        html = html.replace("<!-- SEARCH_VALUE -->", searchDestination != null ? escapeHtml(searchDestination) : "");
        html = html.replace("<!-- PACKAGE_CARDS -->", cardsBuilder.toString());

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
