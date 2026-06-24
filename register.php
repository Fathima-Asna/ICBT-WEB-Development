<?php
session_start();
// If already logged in, redirect to correct dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - GlobeTrek Adventures</title>
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
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <li><a href="login.php" class="nav-link active">Sign In</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Register as a Traveler to explore and book packages</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div style="background-color: #fed7d7; color: #9b2c2c; padding: 0.75rem 1rem; border-radius: var(--border-radius-md); margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center; border: 1px solid #feb2b2;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <form id="register-form" method="POST" action="api/register.php" onsubmit="if (this.password.value !== this['confirm-password'].value) { alert('Passwords do not match.'); return false; } document.getElementById('btn-register').innerHTML='Creating Account...';">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" placeholder="Choose a strong password" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm-password">Confirm Password</label>
                    <input class="form-control" type="password" id="confirm-password" name="confirm-password" placeholder="Repeat your password" required>
                </div>

                <button class="btn-cta btn-full" type="submit" id="btn-register">Sign Up</button>
            </form>
            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <span style="color: var(--text-muted);">Already have an account? </span>
                <a href="login.php" style="color: var(--primary-color); font-weight: 600; text-decoration: underline;">Sign In</a>
            </div>
        </div>
    </div>

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
