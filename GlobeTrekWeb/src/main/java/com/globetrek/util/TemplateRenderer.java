package com.globetrek.util;

import javax.servlet.ServletContext;
import java.io.*;
import java.nio.charset.StandardCharsets;

/**
 * TemplateRenderer — Helper utility to read static HTML files from the webapp
 * context as UTF-8 strings. Servlets can then use .replace() to substitute
 * server-side SQL data dynamically.
 */
public class TemplateRenderer {

    /**
     * Reads the HTML template at the specified webapp path.
     *
     * @param context  The ServletContext
     * @param htmlPath The relative path of the file in the webapp (e.g. "/login.html")
     * @return The HTML content as a String
     * @throws IOException If file reading fails
     */
    public static String render(ServletContext context, String htmlPath) throws IOException {
        String realPath = context.getRealPath(htmlPath);
        if (realPath == null) {
            throw new FileNotFoundException("HTML Template not found: " + htmlPath);
        }

        StringBuilder sb = new StringBuilder();
        try (BufferedReader br = new BufferedReader(
                new InputStreamReader(new FileInputStream(realPath), StandardCharsets.UTF_8))) {
            String line;
            while ((line = br.readLine()) != null) {
                sb.append(line).append("\n");
            }
        }
        return sb.toString();
    }
}
