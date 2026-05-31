package com.globetrek.servlet;

import com.globetrek.util.DBConnection;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * ContactServlet — Captures digital concierge support inquiries and saves them
 * to the MySQL database inquiries table.
 */
@WebServlet("/ContactServlet")
public class ContactServlet extends HttpServlet {
    
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        String fullName = request.getParameter("full_name");
        String email = request.getParameter("email");
        String mobileNumber = request.getParameter("mobile_number");
        String inquiryType = request.getParameter("inquiry_type");
        String message = request.getParameter("message");
        
        String sql = "INSERT INTO inquiries (full_name, email, mobile_number, inquiry_type, message) VALUES (?, ?, ?, ?, ?)";
        
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
             
            stmt.setString(1, fullName);
            stmt.setString(2, email);
            stmt.setString(3, mobileNumber);
            stmt.setString(4, inquiryType);
            stmt.setString(5, message);
            
            stmt.executeUpdate();
            
            // Redirect back to contact page with a success flag
            response.sendRedirect("contact.html?success=true");
            
        } catch (SQLException | ClassNotFoundException e) {
            e.printStackTrace();
            response.sendRedirect("contact.html?error=true");
        }
    }
}
