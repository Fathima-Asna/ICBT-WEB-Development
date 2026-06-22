<?php
session_start();
require_once 'config/db.php';

// Fetch packages list for inquiry dropdown
try {
    $stmt = $pdo->query("SELECT id, destination FROM packages ORDER BY destination ASC");
    $packages = $stmt->fetchAll();
} catch (\PDOException $e) {
    $packages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Get in touch with GlobeTrek Adventures. Ask questions about our Sri Lanka travel packages.">
    <title>Contact Us - GlobeTrek Adventures</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="packages.php" class="nav-link">Packages</a></li>
                <li><a href="contact.php" class="nav-link active">Contact</a></li>
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

    <!-- Hero -->
    <section class="hero" style="padding: 3.5rem 2rem;">
        <div class="hero-content">
            <h1>Contact GlobeTrek Support</h1>
            <p>Have questions about Dutch canal safaris or Ella panoramic rail tours? Write to us below.</p>
        </div>
    </section>

    <!-- Contact & Inquiries Layout -->
    <main class="container">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:3rem;">
            
            <!-- Contact info card -->
            <div class="dashboard-section" style="display:flex; flex-direction:column; gap:1.5rem;">
                <h3>Our Headquarters</h3>
                <div>
                    <strong style="color:var(--primary-color);">Office Address</strong>
                    <p style="color:var(--text-muted); margin-top:0.25rem;">45, Porutota Road, Negombo, Sri Lanka</p>
                </div>
                <div>
                    <strong style="color:var(--primary-color);">Booking Hotline</strong>
                    <p style="color:var(--text-muted); margin-top:0.25rem;">+94 (31) 222-3456</p>
                </div>
                <div>
                    <strong style="color:var(--primary-color);">General Support Email</strong>
                    <p style="color:var(--text-muted); margin-top:0.25rem;">support@globetrek.lk</p>
                </div>
                <div>
                    <strong style="color:var(--primary-color);">Office Hours</strong>
                    <p style="color:var(--text-muted); margin-top:0.25rem;">Monday - Saturday: 8:30 AM - 5:30 PM (SLST)</p>
                </div>
            </div>

            <!-- Inquiry submission form -->
            <div class="dashboard-section">
                <h3>Submit an Inquiry</h3>
                <form id="contact-inquiry-form" style="margin-top: 1.5rem; display:flex; flex-direction:column; gap:1.5rem;">
                    
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="pkg-select">Select Tour Package</label>
                        <select class="form-control" id="pkg-select" required>
                            <option value="">-- Choose a Package --</option>
                            <?php foreach ($packages as $pkg): ?>
                                <option value="<?= $pkg['id'] ?>"><?= htmlspecialchars($pkg['destination']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="question-text">Your Inquiry / Question</label>
                        <textarea class="form-control" id="question-text" rows="4" placeholder="Tell us what you would like to know about this package..." required style="resize:vertical;"></textarea>
                    </div>

                    <div>
                        <button class="btn-cta btn-full btn-loading-action" type="submit" id="btn-submit-inquiry">Send Inquiry</button>
                    </div>

                </form>
            </div>

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
        document.getElementById('contact-inquiry-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-inquiry');
            const pkgSelect = document.getElementById('pkg-select');
            const questionText = document.getElementById('question-text');

            const packageId = pkgSelect.value;
            const question = questionText.value.trim();

            if (!packageId) {
                if (typeof showToast === 'function') {
                    showToast('Please select a package.', 'warning');
                }
                return;
            }

            if (!question) {
                if (typeof showToast === 'function') {
                    showToast('Please type your inquiry first.', 'warning');
                }
                return;
            }

            if (typeof setButtonLoading === 'function') {
                setButtonLoading(btn, 'Sending...');
            }

            try {
                const response = await fetch('api/submit-query.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ package_id: packageId, question_text: question })
                });
                const result = await response.json();

                if (result.success) {
                    if (typeof showToast === 'function') {
                        showToast(result.message || 'Inquiry submitted successfully!', 'success');
                    }
                    pkgSelect.value = '';
                    questionText.value = '';
                } else {
                    if (typeof showToast === 'function') {
                        showToast(result.message || 'Failed to submit inquiry.', 'error');
                    }
                }
            } catch (err) {
                if (typeof showToast === 'function') {
                    showToast('Network error, please try again.', 'error');
                }
            } finally {
                if (typeof resetButtonLoading === 'function') {
                    resetButtonLoading(btn);
                }
            }
        });
    </script>
</body>
</html>
