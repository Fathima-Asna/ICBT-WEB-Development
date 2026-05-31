package com.globetrek.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * DBConnection — Singleton JDBC Connection Factory for GlobeTrek Adventures.
 *
 * Uses the Singleton pattern to provide a single reusable database connection.
 * Standard JDBC — no frameworks, no connection pools, no Hibernate.
 *
 * Usage:
 *   Connection conn = DBConnection.getConnection();
 *   // ... use conn for PreparedStatements ...
 *
 * Configuration:
 *   Update the URL, USER, and PASSWORD constants below to match your
 *   local MySQL installation.
 */
public class DBConnection {

    // ── Database Configuration ─────────────────────────────────────────────
    private static final String URL  = "jdbc:mysql://localhost:3306/globetrek_db?useSSL=false&serverTimezone=UTC";
    private static final String USER = "root";
    private static final String PASS = "";

    // ── Singleton Instance ─────────────────────────────────────────────────
    private static Connection connection = null;

    // Private constructor — prevents instantiation from outside
    private DBConnection() { }

    /**
     * Returns a singleton database connection.
     * If the connection is null or closed, a new one is created.
     * The JDBC driver is loaded explicitly for compatibility with older Tomcat versions.
     *
     * @return a live Connection to the globetrek_db MySQL database
     * @throws SQLException if a database access error occurs
     * @throws ClassNotFoundException if the MySQL JDBC driver is not found
     */
    public static Connection getConnection() throws SQLException, ClassNotFoundException {
        if (connection == null || connection.isClosed()) {
            // Load the MySQL JDBC driver class into the JVM
            Class.forName("com.mysql.cj.jdbc.Driver");

            // Establish the connection using DriverManager
            connection = DriverManager.getConnection(URL, USER, PASS);
        }
        return connection;
    }
}
