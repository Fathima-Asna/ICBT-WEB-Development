<?php
session_start();
require_once 'config/db.php';

// Route guards
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

try {
    // 1. Fetch staff accounts
    $stmt = $pdo->query("SELECT id, username, role FROM users WHERE role = 'staff' ORDER BY username ASC");
    $staff_members = $stmt->fetchAll();

    // 2. Fetch statistics
    // Total Customers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetchColumn();

    // Total Bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $total_bookings = $stmt->fetchColumn();

    // Pending Questions
    $stmt = $pdo->query("SELECT COUNT(*) FROM queries WHERE status = 'Pending'");
    $pending_queries = $stmt->fetchColumn();

    // Total Likes
    $stmt = $pdo->query("SELECT SUM(likes_count) FROM packages");
    $total_likes = $stmt->fetchColumn() ?: 0;

    // 3. Package Popularity Report (Likes, Bookmarks, and Bookings counts)
    $report_sql = "
        SELECT p.id, p.destination, p.price, p.likes_count,
            (SELECT COUNT(*) FROM saved_packages WHERE package_id = p.id) AS bookmarks_count,
            (SELECT COUNT(*) FROM bookings WHERE package_id = p.id) AS bookings_count
        FROM packages p
        ORDER BY p.likes_count DESC, bookings_count DESC
    ";
    $stmt = $pdo->query($report_sql);
    $package_reports = $stmt->fetchAll();

} catch (\PDOException $e) {
    die("Error loading admin reports: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GlobeTrek Adventures</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="admin.php" class="nav-link active">Admin Space</a></li>
                <li><span class="user-tag">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span></li>
                <li><a href="logout.php" class="btn-secondary" style="padding: 0.5rem 1rem;">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="container">
        <h2 class="section-title">Administrator System Analytics</h2>

        <!-- Stat Cards Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?= $total_customers ?></div>
                <div class="stat-lbl">Registered Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= $total_bookings ?></div>
                <div class="stat-lbl">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= $pending_queries ?></div>
                <div class="stat-lbl">Pending Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= $total_likes ?></div>
                <div class="stat-lbl">Aggregate Likes</div>
            </div>
        </div>

        <div class="dashboard-grid">
            
            <!-- Package Popularity Report -->
            <section class="dashboard-section">
                <h3>Package Performance & Popularity Report</h3>
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Package ID</th>
                                <th>Destination</th>
                                <th>Price</th>
                                <th>Likes count</th>
                                <th>Bookmarks count</th>
                                <th>Bookings count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($package_reports as $rep): ?>
                                <tr>
                                    <td>#<?= $rep['id'] ?></td>
                                    <td style="font-weight: 600; color: var(--primary-color);"><?= htmlspecialchars($rep['destination']) ?></td>
                                    <td>$<?= number_format($rep['price'], 2) ?></td>
                                    <td style="font-weight: 600;">👍 <?= $rep['likes_count'] ?></td>
                                    <td style="font-weight: 600; color: var(--accent-color);">★ <?= $rep['bookmarks_count'] ?></td>
                                    <td style="font-weight: 600; color: var(--success);">✈️ <?= $rep['bookings_count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Staff Accounts List -->
            <section class="dashboard-section">
                <h3>Agency Staff Accounts</h3>
                <div class="data-table-wrapper">
                    <?php if (count($staff_members) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Assigned Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_members as $member): ?>
                                    <tr>
                                        <td>#<?= $member['id'] ?></td>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($member['username']) ?></td>
                                        <td>
                                            <span class="badge" style="background-color: var(--primary-light); color: var(--primary-color);">
                                                <?= htmlspecialchars($member['role']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">No staff accounts registered in the database.</p>
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
