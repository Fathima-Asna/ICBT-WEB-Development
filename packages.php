<?php
session_start();
require_once 'config/db.php';

// Fetch all packages (with optional search filter)
try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    if ($search !== '') {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE destination LIKE ? OR description LIKE ? ORDER BY id ASC");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM packages ORDER BY id ASC");
    }
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

    <!-- Alert Notices -->
    <?php if (isset($_GET['error'])): ?>
        <div style="background-color: #fed7d7; color: #9b2c2c; padding: 1rem; text-align: center; font-weight: 600; border-bottom: 1px solid #feb2b2;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div style="background-color: #c6f6d5; color: #22543d; padding: 1rem; text-align: center; font-weight: 600; border-bottom: 1px solid #9ae6b4;">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

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
        <form method="GET" action="packages.php" style="max-width: 600px; margin: 0 auto 3rem auto; text-align: center; display: flex; gap: 1rem; justify-content: center; align-items: flex-end;">
            <div style="flex-grow: 1; text-align: left;">
                <label class="form-label" style="font-weight: 600; font-size: 1rem; color: var(--primary-color);">Search Destinations</label>
                <input class="form-control" type="text" name="search" placeholder="e.g. Ella, Sigiriya, Dutch Canals..." value="<?= htmlspecialchars($search) ?>" style="padding: 1rem 1.5rem; border-radius: var(--border-radius-lg); font-size: 1.1rem; box-shadow: var(--shadow-sm);">
            </div>
            <button class="btn-cta" type="submit" style="padding: 1rem 2rem;">Search</button>
        </form>

        <div class="package-grid" id="packages-list">
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
                            <form method="POST" action="api/toggle-save.php" style="display: inline;">
                                <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                <button class="btn-save <?= $is_saved ? 'saved' : '' ?>" type="submit" title="<?= $is_saved ? 'Saved to bookmarks' : 'Save for later' ?>">
                                    <span class="star-icon"><?= $is_saved ? '★' : '☆' ?></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="package-content">
                        <h3 class="package-title"><?= htmlspecialchars($pkg['destination']) ?></h3>
                        <p class="package-desc"><?= htmlspecialchars($pkg['description']) ?></p>
                        
                        <div class="package-actions">
                            <!-- Like Button -->
                            <form method="POST" action="api/toggle-like.php" style="display: inline;">
                                <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                <button class="btn-like" type="submit">
                                    <span>👍 Like</span>
                                    <span class="like-count"><?= $pkg['likes_count'] ?></span>
                                </button>
                            </form>

                            <!-- Book Now CTA -->
                            <form method="POST" action="api/book-package.php" style="display: inline;">
                                <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                <button class="btn-cta" type="submit" onclick="this.innerHTML='Booking...';">
                                    Book Now
                                </button>
                            </form>
                        </div>

                        <!-- Ask Question Form -->
                        <form class="package-query-form" method="POST" action="api/submit-query.php">
                            <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                            <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Ask a Question about this Package</label>
                            <div class="query-input-group">
                                <input class="query-input" name="question_text" type="text" placeholder="Is transport from Colombo included?" required>
                                <button class="btn-secondary" type="submit" style="padding: 0.5rem 1rem;">Ask</button>
                            </div>
                        </form>

                        <!-- Reviews Section -->
                        <div class="reviews-section" style="margin-top: 1.5rem; border-top: 1px dashed var(--border-color); padding-top: 1rem;">
                            <h4 style="font-size: 0.95rem; color: var(--primary-color); margin-bottom: 0.75rem;">Guest Reviews & Ratings</h4>
                            
                            <?php
                            // Fetch reviews for this package
                            try {
                                $review_stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.package_id = ? ORDER BY r.created_at DESC");
                                $review_stmt->execute([$pkg['id']]);
                                $reviews = $review_stmt->fetchAll();
                            } catch (\PDOException $e) {
                                $reviews = [];
                            }
                            ?>
                            
                            <?php if (count($reviews) > 0): ?>
                                <div style="max-height: 150px; overflow-y: auto; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.25rem;">
                                    <?php foreach ($reviews as $rev): ?>
                                        <div style="background-color: var(--bg-color); padding: 0.5rem; border-radius: var(--border-radius-sm); font-size: 0.85rem; border: 1px solid var(--border-color);">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                                <strong style="color: var(--primary-color);"><?= htmlspecialchars($rev['username']) ?></strong>
                                                <span style="color: var(--accent-color); font-weight: bold;"><?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5 - $rev['rating']) ?></span>
                                            </div>
                                            <p style="color: var(--text-color); line-height: 1.3; font-style: italic;">"<?= htmlspecialchars($rev['comment_text']) ?>"</p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: var(--text-muted); font-size: 0.85rem; font-style: italic; margin-bottom: 1rem;">No reviews yet. Be the first to review!</p>
                            <?php endif; ?>

                            <!-- Review Submission Form -->
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                                <form method="POST" action="api/submit-review.php" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">Your Rating:</label>
                                        <select class="form-control" name="rating" required style="padding: 0.35rem 0.5rem; font-size: 0.85rem; width: auto; height: auto;">
                                            <option value="5">5 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="2">2 Stars</option>
                                            <option value="1">1 Star</option>
                                        </select>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <textarea class="form-control" name="comment_text" rows="2" placeholder="Write a comment..." required style="padding: 0.5rem; font-size: 0.85rem; resize: vertical;"></textarea>
                                        <button class="btn-cta" type="submit" style="padding: 0.5rem 1rem; font-size: 0.85rem; height: fit-content;" onclick="this.innerHTML='Saving...';">Post</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;"><a href="login.php" style="color: var(--primary-color); text-decoration: underline;">Sign In</a> to rate and review this tour package.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($packages) === 0): ?>
            <div id="no-results-msg" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                <p style="font-size: 1.2rem;">No tour packages match your search term.</p>
            </div>
        <?php endif; ?>
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
</body>
</html>
