<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:if test="${packageList == null}">
    <c:redirect url="/packages" />
</c:if>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore GlobeTrek Adventures featured tour packages. Use our custom filter to browse alpine, tropical, and cultural expeditions.">
    <title>Featured Adventures | GlobeTrek Adventures</title>
    
    <!-- Link to single comprehensive stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- --- STICKY NAVIGATION BAR --- -->
    <header>
        <div class="container header-container">
            <!-- Brand Identity -->
            <a href="index.jsp" class="brand" id="brand-home-link">
                <!-- Premium SVG Compass Logo -->
                <svg class="brand-logo-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm1-13h-2v4H7v2h4v4h2v-4h4v-2h-4V7z"/>
                </svg>
                <h1 class="brand-name">GlobeTrek<span>Adventures</span></h1>
            </a>

            <!-- Checkbox Hack for Responsive Mobile Menu -->
            <input type="checkbox" id="nav-toggle" class="nav-toggle-input">
            
            <label for="nav-toggle" class="nav-toggle-label" id="hamburger-menu-button">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <!-- Main Menu Links -->
            <ul class="nav-menu" id="navigation-links">
                <li><a href="index.jsp" class="nav-link" id="nav-home">Home</a></li>
                <li><a href="packages" class="nav-link active" id="nav-packages">Packages</a></li>
                <li><a href="accommodations.html" class="nav-link" id="nav-stays">Stays</a></li>
                <li><a href="contact.html" class="nav-link" id="nav-contact">Contact</a></li>
            </ul>

            <!-- Call to Actions -->
            <div class="nav-cta" id="header-cta-buttons">
                <a href="login.jsp" class="btn btn-outline" style="padding: 10px 22px; font-size: 0.85rem;" id="cta-login">Login</a>
                <a href="signup.jsp" class="btn btn-primary" style="padding: 10px 22px; font-size: 0.85rem;" id="cta-signup">Sign Up</a>
            </div>
        </div>
    </header>

    <!-- --- MAIN CONTENT --- -->
    <main>
        <section class="section-padding" style="background-color: var(--light);" id="packages-page-content">
            <div class="container">
                <div class="text-center mb-50" style="max-width: 700px; margin-left: auto; margin-right: auto;">
                    <h2 class="decorated decorated-center">Curated Expeditions</h2>
                    <p style="font-size: 1.15rem; opacity: 0.85;">Embark on journeys meticulously designed to balance breathtaking exploration with premium luxury. Search or filter by destination below.</p>
                </div>

                <!-- --- PREMIUM CONCIERGE SEARCH BAR --- -->
                <div class="search-container" style="max-width: 600px; margin: -20px auto 40px auto; padding: 20px; background: var(--white); border-radius: 12px; box-shadow: var(--shadow-sm);">
                    <form action="packages" method="GET" style="display: flex; gap: 12px; width: 100%;">
                        <div class="input-wrapper" style="flex: 1; margin: 0;">
                            <input type="text" name="destination" class="form-input" style="margin: 0; width: 100%; border-color: var(--gray-light);" placeholder="Search destinations (e.g., Japan, Switzerland)..." value="${fn:escapeXml(param.destination)}">
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 12px 28px; white-space: nowrap;">Search Concierge</button>
                        <c:if test="${not empty param.destination}">
                            <a href="packages" class="btn btn-outline" style="padding: 12px 20px; display: flex; align-items: center; justify-content: center; text-decoration: none;">Clear</a>
                        </c:if>
                    </form>
                </div>

                <!-- Pure CSS Tab Filtering Setup (Only shown when not showing search results to prevent confusion) -->
                <c:if test="${empty param.destination}">
                    <input type="radio" class="filter-radio" id="filter-all" name="package-filter" checked>
                    <input type="radio" class="filter-radio" id="filter-alpine" name="package-filter">
                    <input type="radio" class="filter-radio" id="filter-tropical" name="package-filter">
                    <input type="radio" class="filter-radio" id="filter-cultural" name="package-filter">

                    <!-- Filter Labels Control Bar -->
                    <div class="filter-tabs" id="packages-filter-tabs">
                        <label class="filter-label" for="filter-all">All Adventures</label>
                        <label class="filter-label" for="filter-alpine">Alpine Peaks</label>
                        <label class="filter-label" for="filter-tropical">Tropical Reefs</label>
                        <label class="filter-label" for="filter-cultural">Cultural Trails</label>
                    </div>
                </c:if>

                <!-- Output Packages Grid -->
                <div class="packages-grid" id="packages-display-grid">
                    <c:forEach var="pkg" items="${packageList}">
                        <!-- Category mapper to dynamically preserve CSS filter behaviors and visuals -->
                        <c:set var="category" value="cultural" />
                        <c:set var="badge" value="Cultural" />
                        <c:set var="image" value="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=800&auto=format&fit=crop" />
                        <c:set var="duration" value="7 Days" />
                        <c:set var="rating" value="4.9 (98 reviews)" />
                        
                        <c:choose>
                            <c:when test="${fn:containsIgnoreCase(pkg.name, 'swiss') or fn:containsIgnoreCase(pkg.name, 'alpine') or fn:containsIgnoreCase(pkg.destination, 'switzerland')}">
                                <c:set var="category" value="alpine" />
                                <c:set var="badge" value="Alpine" />
                                <c:set var="image" value="https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=800&auto=format&fit=crop" />
                                <c:set var="duration" value="8 Days" />
                                <c:set var="rating" value="4.9 (124 reviews)" />
                            </c:when>
                            <c:when test="${fn:containsIgnoreCase(pkg.name, 'bali') or fn:containsIgnoreCase(pkg.name, 'beach') or fn:containsIgnoreCase(pkg.destination, 'indonesia')}">
                                <c:set var="category" value="tropical" />
                                <c:set var="badge" value="Tropical" />
                                <c:set var="image" value="https://images.unsplash.com/photo-1537996194471-e657df975ab4?q=80&w=800&auto=format&fit=crop" />
                                <c:set var="duration" value="10 Days" />
                                <c:set var="rating" value="4.8 (210 reviews)" />
                            </c:when>
                            <c:when test="${fn:containsIgnoreCase(pkg.name, 'kyoto') or fn:containsIgnoreCase(pkg.name, 'heritage') or fn:containsIgnoreCase(pkg.destination, 'japan')}">
                                <c:set var="category" value="cultural" />
                                <c:set var="badge" value="Cultural" />
                                <c:set var="image" value="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=800&auto=format&fit=crop" />
                                <c:set var="duration" value="7 Days" />
                                <c:set var="rating" value="4.9 (98 reviews)" />
                            </c:when>
                            <c:when test="${fn:containsIgnoreCase(pkg.name, 'patagonia') or fn:containsIgnoreCase(pkg.destination, 'argentina')}">
                                <c:set var="category" value="eco" />
                                <c:set var="badge" value="Eco Tour" />
                                <c:set var="image" value="https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=800&auto=format&fit=crop" />
                                <c:set var="duration" value="12 Days" />
                                <c:set var="rating" value="4.7 (86 reviews)" />
                            </c:when>
                        </c:choose>

                        <article class="package-card ${category}" id="package-${pkg.id}">
                            <div class="package-media">
                                <img class="package-img" src="${image}" alt="${fn:escapeXml(pkg.name)}">
                                <span class="package-badge">${badge}</span>
                                <div class="package-meta">
                                    <span class="package-duration">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                        ${duration}
                                    </span>
                                    <span class="package-rating">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        ${rating}
                                    </span>
                                </div>
                            </div>
                            <div class="package-info">
                                <h3>${fn:escapeXml(pkg.name)}</h3>
                                <p>${fn:escapeXml(pkg.description)}</p>
                                <p style="font-size: 0.85rem; color: var(--gray-dark); margin-top: -10px; margin-bottom: 15px;">📍 ${fn:escapeXml(pkg.destination)}</p>
                                <div class="package-footer">
                                    <div class="package-price">
                                        <span>Price per person</span>
                                        <span>$<fmt:formatNumber type="number" maxFractionDigits="2" minFractionDigits="2" value="${pkg.price}" /></span>
                                    </div>
                                    <a href="contact.html" class="btn btn-primary btn-sm" style="padding: 10px 20px; font-size: 0.85rem;">Book Tour</a>
                                </div>
                            </div>
                        </article>
                    </c:forEach>
                    
                    <c:if test="${empty packageList}">
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--white); border-radius: 12px; box-shadow: var(--shadow-sm);">
                            <p style="font-size: 1.2rem; color: var(--gray-dark);">No expeditions found matching your destination.</p>
                            <a href="packages" class="btn btn-primary" style="margin-top: 15px; display: inline-block;">View All Adventures</a>
                        </div>
                    </c:if>
                </div>
            </div>
        </section>

        <!-- --- NEWSLETTER CTA BANNER --- -->
        <section class="section-padding" style="background-color: var(--white);">
            <div class="container">
                <div class="newsletter-banner" id="newsletter-form-container">
                    <h2>Join the Wanderlust Circle</h2>
                    <p>Subscribe to receive curated secret trails, seasonal flight sales, and architectural stay recommendations directly in your inbox. No spam, ever.</p>
                    <form action="ContactServlet" method="POST" class="footer-newsletter-form" style="max-width: 500px; margin: 0 auto;">
                        <input type="email" placeholder="Enter your email address" class="footer-newsletter-input" required aria-label="Email Address for newsletter">
                        <button type="submit" class="footer-newsletter-btn" id="newsletter-submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- --- FOOTER --- -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: About -->
                <div class="footer-col" id="footer-about">
                    <h4>GlobeTrek</h4>
                    <p>GlobeTrek Adventures designs bespoke journeys that inspire wonder, protect heritage, and respect natural ecosystems across the globe.</p>
                    <div class="footer-socials">
                        <a href="https://facebook.com" class="footer-social-icon" aria-label="Facebook Link">
                            <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79z"/></svg>
                        </a>
                        <a href="https://instagram.com" class="footer-social-icon" aria-label="Instagram Link">
                            <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                        </a>
                        <a href="https://twitter.com" class="footer-social-icon" aria-label="Twitter Link">
                            <svg viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Destinations -->
                <div class="footer-col" id="footer-destinations">
                    <h4>Destinations</h4>
                    <ul class="footer-links">
                        <li><a href="packages">Swiss Alps Peaks</a></li>
                        <li><a href="packages">Bali & Gili Islands</a></li>
                        <li><a href="packages">Ancient Kyoto</a></li>
                        <li><a href="packages">Patagonia Expeditions</a></li>
                    </ul>
                </div>

                <!-- Col 3: Company -->
                <div class="footer-col" id="footer-company">
                    <h4>GlobeTrek Info</h4>
                    <ul class="footer-links">
                        <li><a href="index.jsp#why-choose-us">About Our Mission</a></li>
                        <li><a href="contact.html">Contact Support</a></li>
                        <li><a href="signup.jsp">Join Travel Club</a></li>
                        <li><a href="login.jsp">Agency Login</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div class="footer-col" id="footer-newsletter">
                    <h4>Newsletter</h4>
                    <p>Get exclusive monthly escape itineraries, seasonal packages, and off-grid insights.</p>
                    <form action="ContactServlet" method="POST" class="footer-newsletter-form">
                        <input type="email" placeholder="Join our circle" class="footer-newsletter-input" required aria-label="Email address for subscription">
                        <button type="submit" class="footer-newsletter-btn" aria-label="Submit newsletter">
                            <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: var(--white);"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-copyright">
                    &copy; 2026 GlobeTrek Adventures Ltd. All rights reserved. Made with absolute care by Expert UI/UX.
                </div>
                <ul class="footer-policies">
                    <li><a href="index.jsp">Privacy Policy</a></li>
                    <li><a href="index.jsp">Terms of Service</a></li>
                    <li><a href="index.jsp">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </footer>

</body>
</html>
