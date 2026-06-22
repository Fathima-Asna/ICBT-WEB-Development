<?php
session_start();
require_once 'config/db.php';

// Fetch top 3 featured packages (sorted by likes)
try {
    $stmt = $pdo->query("SELECT * FROM packages ORDER BY likes_count DESC LIMIT 3");
    $packages = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// Fetch saved packages if customer is logged in
$saved_packages = [];
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT package_id FROM saved_packages WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $saved_packages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (\PDOException $e) {
        // Silent fail
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover premium Sri Lankan adventures. Book custom packages in Negombo, Ella, and Sigiriya.">
    <title>GlobeTrek Adventures - Sri Lankan Tours</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="packages.php" class="nav-link">Packages</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'customer'): ?>
                        <li><a href="dashboard.php" class="nav-link">My Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php" class="nav-link">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><span class="user-tag"><?= htmlspecialchars($_SESSION['username']) ?></span></li>
                    <li><a href="logout.php" class="btn-secondary" style="padding: 0.5rem 1rem;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-cta">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Sri Lanka's Untamed Beauty</h1>
            <p>From historic Dutch canals in Negombo to ancient sky palaces in Sigiriya and scenic rail tours in Ella. Your premium getaway awaits.</p>
            <a href="packages.php" class="btn-cta" style="font-size:1.1rem; padding: 1rem 2rem;">Explore All Packages</a>
        </div>
    </section>

    <!-- Trust Badges / Stats -->
    <section style="background-color: var(--white); border-bottom: 1px solid var(--border-color); padding: 3rem 2rem;">
        <div class="container" style="padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
            <div>
                <h3 style="font-size: 2.5rem; color: var(--primary-color); font-weight: 800;">1,500+</h3>
                <p style="color: var(--text-muted); font-weight: 500;">Happy Travelers</p>
            </div>
            <div>
                <h3 style="font-size: 2.5rem; color: var(--primary-color); font-weight: 800;">15+</h3>
                <p style="color: var(--text-muted); font-weight: 500;">Years of Curation</p>
            </div>
            <div>
                <h3 style="font-size: 2.5rem; color: var(--primary-color); font-weight: 800;">4.9 ★</h3>
                <p style="color: var(--text-muted); font-weight: 500;">Average Rating</p>
            </div>
        </div>
    </section>

    <!-- Featured Packages -->
    <main class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2.5rem;">
            <h2 class="section-title" style="margin-bottom:0;">Featured Tour Packages</h2>
            <a href="packages.php" style="color: var(--primary-color); font-weight:700; border-bottom: 2px solid var(--accent-color); padding-bottom: 2px;">View All Packages &rarr;</a>
        </div>
        
        <div class="package-grid">
            <?php foreach ($packages as $pkg): ?>
                <?php 
                    $is_saved = in_array($pkg['id'], $saved_packages);
                ?>
                <div class="package-card">
                    <div class="package-image-container">
                        <img class="package-image" src="<?= htmlspecialchars($pkg['image_url']) ?>" alt="<?= htmlspecialchars($pkg['destination']) ?>">
                        <div class="package-price-tag">$<?= number_format($pkg['price'], 2) ?></div>
                        
                        <!-- Bookmark Star Button -->
                        <div class="package-save-tag">
                            <button 
                                class="btn-save <?= $is_saved ? 'saved' : '' ?>" 
                                onclick="toggleSave(<?= $pkg['id'] ?>, this)"
                                title="<?= $is_saved ? 'Saved to bookmarks' : 'Save for later' ?>"
                            >
                                <span class="star-icon"><?= $is_saved ? '★' : '☆' ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="package-content">
                        <h3 class="package-title"><?= htmlspecialchars($pkg['destination']) ?></h3>
                        <p class="package-desc"><?= htmlspecialchars($pkg['description']) ?></p>
                        
                        <div class="package-actions">
                            <!-- Like Button -->
                            <button class="btn-like" onclick="toggleLike(<?= $pkg['id'] ?>, this)">
                                <span>👍 Like</span>
                                <span class="like-count"><?= $pkg['likes_count'] ?></span>
                            </button>

                            <!-- Book Now CTA -->
                            <button class="btn-cta" onclick="bookPackage(<?= $pkg['id'] ?>, this)">
                                Book Now
                            </button>
                        </div>

                        <!-- Ask Question Form -->
                        <form class="package-query-form" data-package-id="<?= $pkg['id'] ?>">
                            <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Ask a Question about this Package</label>
                            <div class="query-input-group">
                                <input class="query-input" type="text" placeholder="Is lunch included?" required>
                                <button class="btn-secondary" type="submit" style="padding: 0.5rem 1rem;">Ask</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Why Us Section -->
    <section style="background-color: var(--white); padding: 5rem 2rem;">
        <div class="container" style="padding:0;">
            <h2 class="section-title">Why Travel With GlobeTrek?</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem; margin-top: 3rem;">
                <div>
                    <h4 style="color:var(--primary-color); font-size:1.2rem; font-weight:700; margin-bottom:0.5rem;">Authentic Local Curators</h4>
                    <p style="color:var(--text-muted); font-size:0.95rem;">Our guides and experts reside in Negombo and the hill country, ensuring you see the authentic side of Sri Lanka away from commercial crowds.</p>
                </div>
                <div>
                    <h4 style="color:var(--primary-color); font-size:1.2rem; font-weight:700; margin-bottom:0.5rem;">Secure Relational Operations</h4>
                    <p style="color:var(--text-muted); font-size:0.95rem;">Our customer-first software stack secures your bookmarks and questions. Our team is instantly notified to reply to queries and handle booking updates.</p>
                </div>
                <div>
                    <h4 style="color:var(--primary-color); font-size:1.2rem; font-weight:700; margin-bottom:0.5rem;">Sustainable Travel</h4>
                    <p style="color:var(--text-muted); font-size:0.95rem;">We support Negombo lagoon preservation efforts and hire local boats and trains, keeping your travel footprint low and community impact high.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>GlobeTrek Adventures</h4>
                <p>Curating authentic travel experiences across the teardrop of India. Explore Negombo, Sigiriya, Ella and beyond.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Browse Packages</a></li>
                    <li><a href="login.php">Admin Sign In</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <p>Email: booking@globetrek.lk<br>Hotline: +94 (11) 234-5678</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 GlobeTrek Adventures (Pvt) Ltd. All Rights Reserved.
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/app.js"></script>
</body>
</html>
