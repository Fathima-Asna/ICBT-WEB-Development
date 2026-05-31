package com.globetrek.servlet;

import com.globetrek.util.DBConnection;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * PackageServlet — Handles searching/filtering travel packages by destination
 * and forwards the results to the packages.jsp dynamic page.
 */
@WebServlet("/packages")
public class PackageServlet extends HttpServlet {
    
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
            
        String searchDestination = request.getParameter("destination");
        List<Map<String, Object>> packageList = new ArrayList<>();
        
        // Base query vs Search query
        String sql = (searchDestination != null && !searchDestination.trim().isEmpty()) 
                     ? "SELECT * FROM packages WHERE destination LIKE ?" 
                     : "SELECT * FROM packages";
                     
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
             
            if (searchDestination != null && !searchDestination.trim().isEmpty()) {
                stmt.setString(1, "%" + searchDestination.trim() + "%");
            }
            
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    Map<String, Object> pkg = new HashMap<>();
                    pkg.put("id", rs.getInt("id"));
                    pkg.put("name", rs.getString("name"));
                    pkg.put("destination", rs.getString("destination"));
                    pkg.put("price", rs.getDouble("price"));
                    pkg.put("description", rs.getString("description"));
                    packageList.add(pkg);
                }
            }
            
            // Bind to request and forward to JSP
            request.setAttribute("packageList", packageList);
            request.getRequestDispatcher("packages.jsp").forward(request, response);
            
        } catch (SQLException | ClassNotFoundException e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Database error");
        }
    }
}
