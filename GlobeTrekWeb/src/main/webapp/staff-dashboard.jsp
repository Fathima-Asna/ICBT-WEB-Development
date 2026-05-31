<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%
    // ── Security Guard ─────────────────────────────────────────────────────────
    HttpSession sess = request.getSession(false);
    String userRole = (sess != null) ? (String) sess.getAttribute("userRole") : null;
    if (sess == null || (!"Staff".equals(userRole) && !"Admin".equals(userRole))) {
        response.sendRedirect(request.getContextPath() + "/login.jsp?error=unauthorized");
        return;
    }

    String userFirstName = (String) sess.getAttribute("userFirstName");
    String userLastName  = (String) sess.getAttribute("userLastName");
    String userEmail     = (String) sess.getAttribute("userEmail");
    if (userFirstName == null) userFirstName = "Staff";
%>
<c:if test="${packageList == null}">
    <c:redirect url="/staff/action" />
</c:if>

<!-- Calculate dynamic stats using pure JSTL loops -->
<c:set var="totalPrice" value="0" />
<c:set var="maxPrice" value="0" />
<c:forEach var="pkg" items="${packageList}">
    <c:set var="totalPrice" value="${totalPrice + pkg.price}" />
    <c:if test="${pkg.price > maxPrice}">
        <c:set var="maxPrice" value="${pkg.price}" />
    </c:if>
</c:forEach>
<c:set var="avgPrice" value="0" />
<c:if test="${fn:length(packageList) > 0}">
    <c:set var="avgPrice" value="${totalPrice / fn:length(packageList)}" />
</c:if>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | GlobeTrek Adventures</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/style.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/dashboard.css">
</head>
<body>
<div class="dash-wrapper">

    <!-- ══════════════════════════════════════════════
         SIDEBAR
         ══════════════════════════════════════════════ -->
    <aside class="dash-sidebar" role="navigation" aria-label="Staff dashboard navigation">
        <a href="${pageContext.request.contextPath}/index.jsp" class="sidebar-brand">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm1-13h-2v4H7v2h4v4h2v-4h4v-2h-4V7z"/></svg>
            <div class="sidebar-brand-name">GlobeTrek<span>Adventures</span></div>
        </a>

        <div class="sidebar-role-badge role-staff"><%= userRole %></div>

        <div class="sidebar-user">
            <div class="sidebar-user-name"><%= userFirstName %> <%= userLastName %></div>
            <div class="sidebar-user-email"><%= userEmail %></div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-section-label">Manage</div>
            <a href="${pageContext.request.contextPath}/staff/action" class="sidebar-nav-item active">
                <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.86.5 13.34.5c-1.3 0-2.43.52-3.34 1.34C9.1 1.02 7.96.5 6.66.5 4.14.5 2 2.54 2 4.66c0 .46.11.9.18 1.34H0v14h24V6h-4zm-6.67-3.83c1.06 0 1.84.78 1.84 1.83s-.78 1.84-1.84 1.84c-1.06 0-1.83-.78-1.83-1.84s.77-1.83 1.83-1.83zM6.66 3.83c1.06 0 1.84.78 1.84 1.84s-.78 1.84-1.84 1.84c-1.06 0-1.83-.78-1.83-1.84s.77-1.84 1.83-1.84zM2 18V8h8v10H2zm10 0V8h10v10H12z"/></svg>
                Manage Packages
            </a>
            <% if ("Admin".equals(userRole)) { %>
            <a href="${pageContext.request.contextPath}/admin/action" class="sidebar-nav-item">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                Admin Panel
            </a>
            <% } %>
        </nav>

        <div class="sidebar-footer">
            <a href="${pageContext.request.contextPath}/logout" class="sidebar-logout">
                <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- ══════════════════════════════════════════════
         MAIN CONTENT
         ══════════════════════════════════════════════ -->
    <main class="dash-main">

        <!-- Page Header -->
        <div class="dash-page-header">
            <h1 class="dash-page-title">Packages Management</h1>
            <p class="dash-page-subtitle">Review, update, and manage all travel tour packages offered by GlobeTrek Adventures.</p>
        </div>

        <!-- ── Feedback Banners ─────────────────────────────────────── -->
        <c:choose>
            <c:when test="${param.success == 'package_updated'}">
                <div class="dash-alert dash-alert--success" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Package description and price updated successfully.
                </div>
            </c:when>
            <c:when test="${param.error == 'missing_fields'}">
                <div class="dash-alert dash-alert--error" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    All update fields are required. Please check your submission.
                </div>
            </c:when>
            <c:when test="${param.error == 'invalid_price'}">
                <div class="dash-alert dash-alert--error" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    Price must be a positive number.
                </div>
            </c:when>
            <c:when test="${param.error == 'invalid_formats'}">
                <div class="dash-alert dash-alert--error" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    Invalid number formatting. Price must be a valid decimal number.
                </div>
            </c:when>
            <c:when test="${param.error == 'package_not_found'}">
                <div class="dash-alert dash-alert--error" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    Selected package was not found.
                </div>
            </c:when>
            <c:when test="${param.error == 'db_error'}">
                <div class="dash-alert dash-alert--error" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    Database query failure. Please try again.
                </div>
            </c:when>
        </c:choose>

        <!-- ── Stats Strip ──────────────────────────────────────────── -->
        <div class="dash-stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.86.5 13.34.5c-1.3 0-2.43.52-3.34 1.34C9.1 1.02 7.96.5 6.66.5 4.14.5 2 2.54 2 4.66c0 .46.11.9.18 1.34H0v14h24V6h-4zm-6.67-3.83c1.06 0 1.84.78 1.84 1.83s-.78 1.84-1.84 1.84c-1.06 0-1.83-.78-1.83-1.84s.77-1.83 1.83-1.83zM6.66 3.83c1.06 0 1.84.78 1.84 1.84s-.78 1.84-1.84 1.84c-1.06 0-1.83-.78-1.83-1.84s.77-1.84 1.83-1.84zM2 18V8h8v10H2zm10 0V8h10v10H12z"/></svg>
                </div>
                <div class="stat-card-value">${fn:length(packageList)}</div>
                <div class="stat-card-label">Total Packages</div>
            </div>
            <div class="stat-card accent-teal">
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-6h2v6zm0-8h-2V7h2v4z"/></svg>
                </div>
                <div class="stat-card-value">$<fmt:formatNumber type="number" maxFractionDigits="2" value="${avgPrice}" /></div>
                <div class="stat-card-label">Average Price</div>
            </div>
            <div class="stat-card accent-sunset">
                <div class="stat-card-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </div>
                <div class="stat-card-value">$<fmt:formatNumber type="number" maxFractionDigits="2" value="${maxPrice}" /></div>
                <div class="stat-card-label">Maximum Price</div>
            </div>
        </div>

        <!-- ── SECTION 1: Dynamic Packages Management ────────────────── -->
        <section id="bookings-section">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h2 class="dash-panel-title">Active Travel Packages</h2>
                    <span class="badge badge-staff">${fn:length(packageList)} active</span>
                </div>
                <div class="dash-panel-body no-pad">
                    <div class="dash-table-wrapper">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">ID</th>
                                    <th style="width: 20%;">Package Name</th>
                                    <th style="width: 15%;">Destination</th>
                                    <th style="width: 15%;">Price ($)</th>
                                    <th style="width: 32%;">Description</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:forEach var="pkg" items="${packageList}">
                                    <tr>
                                        <td><code>GT-${pkg.id}</code></td>
                                        <td><strong>${fn:escapeXml(pkg.name)}</strong></td>
                                        <td>${fn:escapeXml(pkg.destination)}</td>
                                        
                                        <!-- Inline editing form mapped to StaffServlet -->
                                        <form action="${pageContext.request.contextPath}/staff/action" method="POST">
                                            <input type="hidden" name="action" value="updatePackage">
                                            <input type="hidden" name="packageId" value="${pkg.id}">
                                            
                                            <!-- Editable Price Field -->
                                            <td>
                                                <input type="number" step="0.01" min="0" name="price" 
                                                       value="${pkg.price}" class="dash-form-input" 
                                                       style="padding: 6px 10px; font-size: 0.85rem; width: 110px; margin: 0;" required>
                                            </td>
                                            
                                            <!-- Editable Description Field -->
                                            <td>
                                                <textarea name="description" class="dash-form-textarea" 
                                                          style="padding: 6px 10px; font-size: 0.85rem; width: 100%; height: 50px; margin: 0; min-height: 50px;" required>${fn:escapeXml(pkg.description)}</textarea>
                                            </td>
                                            
                                            <!-- Action Button -->
                                            <td>
                                                <button type="submit" class="btn btn-primary btn-sm" 
                                                        style="padding: 8px 16px; font-size: 0.80rem; width: 100%; margin: 0;">
                                                    Update
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                </c:forEach>
                                
                                <c:if test="${empty packageList}">
                                    <tr>
                                        <td colspan="6" class="dash-table-empty">
                                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                                            No travel packages are currently stored in the database.
                                        </td>
                                    </tr>
                                </c:if>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>
</body>
</html>
