<?php
session_start();
require_once 'config/db.php';

// Route guards
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch saved packages
    $stmt = $pdo->prepare("SELECT p.* FROM packages p JOIN saved_packages s ON p.id = s.package_id WHERE s.user_id = ?");
    $stmt->execute([$user_id]);
    $saved_packages = $stmt->fetchAll();

    // 2. Fetch queries
    $stmt = $pdo->prepare("SELECT q.*, p.destination FROM queries q JOIN packages p ON q.package_id = p.id WHERE q.user_id = ? ORDER BY q.created_at DESC");
    $stmt->execute([$user_id]);
    $queries = $stmt->fetchAll();

    // 3. Fetch bookings
    $stmt = $pdo->prepare("SELECT b.id as booking_id, b.status as booking_status, b.booking_date, p.destination, p.price FROM bookings b JOIN packages p ON b.package_id = p.id WHERE b.user_id = ? ORDER BY b.booking_date DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();

} catch (\PDOException $e) {
    die("Error loading dashboard data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - GlobeTrek Adventures</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="packages.php" class="nav-link">Packages</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <li><a href="dashboard.php" class="nav-link active">My Dashboard</a></li>
                <li><span class="user-tag"><?= htmlspecialchars($_SESSION['username']) ?></span></li>
                <li><a href="logout.php" class="btn-secondary" style="padding: 0.5rem 1rem;">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Workspace Dashboard -->
    <main class="container">
        <h2 class="section-title">My Traveler Space</h2>

        <div class="dashboard-grid">
            
            <!-- Bookings History -->
            <section class="dashboard-section">
                <h3>My Bookings</h3>
                <div class="data-table-wrapper">
                    <?php if (count($bookings) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Package / Destination</th>
                                    <th>Price</th>
                                    <th>Date Booked</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $bk): ?>
                                    <tr>
                                        <td>#<?= $bk['booking_id'] ?></td>
                                        <td style="font-weight:600; color:var(--primary-color);"><?= htmlspecialchars($bk['destination']) ?></td>
                                        <td>$<?= number_format($bk['price'], 2) ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($bk['booking_date'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($bk['booking_status']) ?>">
                                                <?= htmlspecialchars($bk['booking_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">You have no active bookings. Explore packages on the <a href="index.php" style="color:var(--primary-color); font-weight:600;">Home page</a> to book!</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Bookmarked Packages -->
            <section class="dashboard-section">
                <h3>Bookmarks / Saved Packages</h3>
                <?php if (count($saved_packages) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                        <?php foreach ($saved_packages as $pkg): ?>
                            <div class="package-card" id="pkg-card-<?= $pkg['id'] ?>">
                                <div class="package-image-container" style="height: 150px;">
                                    <img class="package-image" src="<?= htmlspecialchars($pkg['image_url']) ?>" alt="<?= htmlspecialchars($pkg['destination']) ?>">
                                </div>
                                <div class="package-content" style="padding: 1.25rem;">
                                    <h4 style="font-size: 1.1rem; color:var(--primary-color); margin-bottom: 0.5rem;"><?= htmlspecialchars($pkg['destination']) ?></h4>
                                    <p style="font-size: 0.85rem; color:var(--text-muted); margin-bottom: 1rem;"><?= number_format($pkg['price'], 2) ?></p>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn-cta" style="padding: 0.5rem 1rem; font-size: 0.8rem;" onclick="bookPackage(<?= $pkg['id'] ?>, this)">Book Now</button>
                                        <button class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem;" onclick="toggleSave(<?= $pkg['id'] ?>, this); document.getElementById('pkg-card-<?= $pkg['id'] ?>').remove();">Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-muted);">No saved packages yet. Keep track of packages by bookmarking them on the homepage.</p>
                <?php endif; ?>
            </section>

            <!-- Query history -->
            <section class="dashboard-section">
                <h3>My Submitted Questions</h3>
                <div class="data-table-wrapper">
                    <?php if (count($queries) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Package</th>
                                    <th>Question</th>
                                    <th>Answer / Status</th>
                                    <th>Submitted Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queries as $q): ?>
                                    <tr>
                                        <td style="font-weight:600;"><?= htmlspecialchars($q['destination']) ?></td>
                                        <td><?= htmlspecialchars($q['question_text']) ?></td>
                                        <td>
                                            <?php if ($q['answer_text']): ?>
                                                <div style="background-color: var(--primary-light); padding: 0.5rem; border-radius: var(--border-radius-sm); font-size: 0.85rem;">
                                                    <?= htmlspecialchars($q['answer_text']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted); font-style:italic;">Awaiting response...</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('Y-m-d H:i', strtotime($q['created_at'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($q['status']) ?>">
                                                <?= htmlspecialchars($q['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">You have not asked any questions yet.</p>
                    <?php endif; ?>
                </div>
            </section>

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
</body>
</html>
