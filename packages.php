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
        // Silent fail
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Search and browse our premium Sri Lankan travel packages. Filter instantly and book securely.">
    <title>Search Packages - GlobeTrek Adventures</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="packages.php" class="nav-link active">Packages</a></li>
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

    <!-- Subpage Hero -->
    <section class="hero" style="padding: 3.5rem 2rem;">
        <div class="hero-content">
            <h1>Browse Our Premium Tours</h1>
            <p>Use the search filter below to find your dream destination in Negombo and across Sri Lanka.</p>
        </div>
    </section>

    <!-- Packages Browser Container -->
    <main class="container">
        
        <!-- Search Bar Section -->
        <div style="max-width: 600px; margin: 0 auto 3rem auto; text-align: center;">
            <label class="form-label" style="font-weight: 600; font-size: 1rem; color: var(--primary-color);">Search Destinations</label>
            <input class="form-control" type="text" id="search-input" placeholder="e.g. Ella, Sigiriya, Dutch Canals..." style="padding: 1rem 1.5rem; text-align: center; border-radius: var(--border-radius-lg); font-size: 1.1rem; box-shadow: var(--shadow-sm);">
        </div>

        <div class="package-grid" id="packages-list">
            <?php foreach ($packages as $pkg): ?>
                <?php 
                    $is_saved = in_array($pkg['id'], $saved_packages);
                ?>
                <div class="package-card" data-destination="<?= htmlspecialchars(strtolower($pkg['destination'])) ?>">
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
                                <input class="query-input" type="text" placeholder="Is transport from Colombo included?" required>
                                <button class="btn-secondary" type="submit" style="padding: 0.5rem 1rem;">Ask</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="no-results-msg" style="display: none; text-align: center; padding: 4rem; color: var(--text-muted);">
            <p style="font-size: 1.2rem;">No tour packages match your search term.</p>
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
    <script>
        // Client-side instant package search filter
        const searchInput = document.getElementById('search-input');
        const cards = document.querySelectorAll('.package-card');
        const noResults = document.getElementById('no-results-msg');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            cards.forEach(card => {
                const dest = card.dataset.destination;
                if (dest.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>
