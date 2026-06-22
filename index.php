<?php
session_start();
require_once 'config/db.php';

// Fetch all packages
try {
    $stmt = $pdo->query("SELECT * FROM packages ORDER BY id ASC");
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
        // Fail silently or log
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore and book premium tour packages in Negombo and across Sri Lanka. Discover canals, rock fortresses, and panoramic mountains.">
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
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'customer'): ?>
                        <li><a href="dashboard.php" class="nav-link">My Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] === 'staff'): ?>
                        <li><a href="staff.php" class="nav-link">Staff Space</a></li>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php" class="nav-link">Admin Space</a></li>
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
            <?php if (!isset($_SESSION['role'])): ?>
                <a href="login.php" class="btn-cta">Start Booking Now</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container">
        <h2 class="section-title">Premium Tour Packages</h2>
        
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
                    <li><a href="login.php">Agent Sign In</a></li>
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
