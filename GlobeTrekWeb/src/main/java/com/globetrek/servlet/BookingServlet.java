package com.globetrek.servlet;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.*;
import java.nio.charset.StandardCharsets;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.UUID;

/**
 * BookingServlet — Processes travel package booking requests submitted by Customers.
 *
 * Mapped to: /book  (POST only)
 *
 * Workflow:
 *   1. Reads the logged-in customer's email from the active HttpSession.
 *   2. Validates all booking form fields.
 *   3. Generates a unique booking ID.
 *   4. Appends a new record to WEB-INF/data/bookings.txt.
 *   5. Redirects back to customer-dashboard.jsp with a success or error parameter.
 *
 * Data format (bookings.txt):
 *   bookingId|customerEmail|packageName|destination|travelDate|travelers|specialNotes|status|submittedAt
 */
public class BookingServlet extends HttpServlet {

    private static final String BOOKINGS_FILE = "bookings.txt";
    private static final String SEPARATOR     = "|";
    private static final String STATUS_PENDING = "Pending";

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");

        // ── Verify session ────────────────────────────────────────────────────
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("userEmail") == null) {
            response.sendRedirect(request.getContextPath() + "/login.jsp?error=session_expired");
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
            response.sendRedirect(request.getContextPath() + "/customer-dashboard.jsp?error=missing_fields");
            return;
        }

        int travelers;
        try {
            travelers = Integer.parseInt(travelersStr);
            if (travelers < 1 || travelers > 50) throw new NumberFormatException();
        } catch (NumberFormatException e) {
            response.sendRedirect(request.getContextPath() + "/customer-dashboard.jsp?error=invalid_travelers");
            return;
        }

        // Sanitize notes — replace pipe chars to prevent file corruption
        specialNotes = specialNotes.replace(SEPARATOR, "-");
        packageName  = packageName.replace(SEPARATOR, "-");
        destination  = destination.replace(SEPARATOR, "-");

        // ── Generate unique booking ID ────────────────────────────────────────
        String bookingId   = "GT-" + UUID.randomUUID().toString().substring(0, 8).toUpperCase();
        String submittedAt = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss").format(new Date());

        // ── Build the record line ─────────────────────────────────────────────
        String record = String.join(SEPARATOR,
            bookingId,
            customerEmail,
            packageName,
            destination,
            travelDate,
            String.valueOf(travelers),
            specialNotes.isEmpty() ? "None" : specialNotes,
            STATUS_PENDING,
            submittedAt
        );

        // ── Append to bookings.txt ────────────────────────────────────────────
        appendToFile(record);

        // ── Redirect with success ─────────────────────────────────────────────
        response.sendRedirect(request.getContextPath() + "/customer-dashboard.jsp?success=booked&id=" + bookingId);
    }

    /**
     * Appends a line to the bookings file (creates file + directories if absent).
     */
    private void appendToFile(String line) throws IOException {
        String dataDir = getServletContext().getRealPath("/WEB-INF/data/");
        File dir = new File(dataDir);
        if (!dir.exists()) dir.mkdirs();

        File file = new File(dir, BOOKINGS_FILE);
        try (BufferedWriter bw = new BufferedWriter(
                new OutputStreamWriter(new FileOutputStream(file, true), StandardCharsets.UTF_8))) {
            bw.write(line);
            bw.newLine();
        }
    }

    /**
     * Trims whitespace. Returns empty string for null input.
     */
    private String sanitize(String input) {
        return (input == null) ? "" : input.trim();
    }
}
