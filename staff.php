<?php
session_start();
require_once 'config/db.php';

// Route guards
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

try {
    // 1. Fetch queries
    $stmt = $pdo->query("SELECT q.*, u.username, p.destination FROM queries q JOIN users u ON q.user_id = u.id JOIN packages p ON q.package_id = p.id ORDER BY q.status ASC, q.created_at DESC");
    $queries = $stmt->fetchAll();

    // 2. Fetch bookings
    $stmt = $pdo->query("SELECT b.*, u.username, p.destination, p.price FROM bookings b JOIN users u ON b.user_id = u.id JOIN packages p ON b.package_id = p.id ORDER BY b.booking_date DESC");
    $bookings = $stmt->fetchAll();

    // 3. Fetch packages for editing
    $stmt = $pdo->query("SELECT * FROM packages ORDER BY id ASC");
    $packages = $stmt->fetchAll();

} catch (\PDOException $e) {
    die("Error loading staff data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - GlobeTrek Adventures</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="staff.php" class="nav-link active">Staff Space</a></li>
                <li><span class="user-tag">Staff: <?= htmlspecialchars($_SESSION['username']) ?></span></li>
                <li><a href="logout.php" class="btn-secondary" style="padding: 0.5rem 1rem;">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="container">
        <h2 class="section-title">Staff Administration Workspace</h2>

        <div class="dashboard-grid">
            
            <!-- Bookings Management -->
            <section class="dashboard-section">
                <h3>Manage Traveler Bookings</h3>
                <div class="data-table-wrapper">
                    <?php if (count($bookings) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Traveler</th>
                                    <th>Package</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>Badge</th>
                                    <th>Update Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $bk): ?>
                                    <tr>
                                        <td>#<?= $bk['id'] ?></td>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($bk['username']) ?></td>
                                        <td><?= htmlspecialchars($bk['destination']) ?></td>
                                        <td>$<?= number_format($bk['price'], 2) ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($bk['booking_date'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($bk['status']) ?>">
                                                <?= htmlspecialchars($bk['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select class="form-control" style="padding:0.4rem; font-size:0.85rem;" 
                                                data-original-val="<?= htmlspecialchars($bk['status']) ?>" 
                                                onchange="updateBookingStatus(<?= $bk['id'] ?>, this)">
                                                <option value="Pending" <?= $bk['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Confirmed" <?= $bk['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="Cancelled" <?= $bk['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">No bookings have been logged yet.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Queries Resolution -->
            <section class="dashboard-section">
                <h3>Customer Questions & Inquiries</h3>
                <div class="data-table-wrapper">
                    <?php if (count($queries) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Traveler</th>
                                    <th>Package</th>
                                    <th>Question</th>
                                    <th>Reply / Answer Status</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queries as $q): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($q['username']) ?></td>
                                        <td><?= htmlspecialchars($q['destination']) ?></td>
                                        <td>"<?= htmlspecialchars($q['question_text']) ?>"</td>
                                        <td class="answer-container">
                                            <?php if ($q['status'] === 'Answered'): ?>
                                                <span style="color:var(--success); font-weight:600">Replied:</span>
                                                <span style="font-size:0.9rem; color:var(--text-muted);"><?= htmlspecialchars($q['answer_text']) ?></span>
                                            <?php else: ?>
                                                <div class="reply-group">
                                                    <input class="form-control reply-input" type="text" placeholder="Type response..." style="padding: 0.5rem; font-size:0.85rem;" required>
                                                    <button class="btn-cta" style="padding:0.5rem 1rem; font-size:0.8rem;" onclick="replyQuery(<?= $q['id'] ?>, this)">Reply</button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
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
                        <p style="color:var(--text-muted);">No customer questions submitted yet.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Packages Modification -->
            <section class="dashboard-section">
                <h3>Modify Tour Packages Content</h3>
                <div style="margin-top: 1.5rem;">
                    <?php foreach ($packages as $pkg): ?>
                        <div class="package-row-edit-card">
                            <h4 style="color:var(--primary-color); margin-bottom: 1rem; font-size:1.1rem; display:flex; align-items:center; gap:0.5rem;">
                                <span>📦</span> Package #<?= $pkg['id'] ?> Details
                            </h4>
                            <div class="editor-form">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" style="font-size:0.8rem;">Destination Title</label>
                                    <input class="form-control edit-dest" type="text" value="<?= htmlspecialchars($pkg['destination']) ?>">
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" style="font-size:0.8rem;">Price (USD)</label>
                                    <input class="form-control edit-price" type="number" step="0.01" value="<?= htmlspecialchars($pkg['price']) ?>">
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1; margin-bottom:0;">
                                    <label class="form-label" style="font-size:0.8rem;">Description</label>
                                    <textarea class="form-control edit-desc" rows="2" style="resize:vertical;"><?= htmlspecialchars($pkg['description']) ?></textarea>
                                </div>
                            </div>
                            <div class="editor-form-footer">
                                <button class="btn-cta" style="font-size:0.85rem; padding:0.6rem 1.2rem;" onclick="savePackageEdit(<?= $pkg['id'] ?>, this)">Save Package Details</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
