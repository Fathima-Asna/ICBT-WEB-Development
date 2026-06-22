<?php
session_start();
// If already logged in, redirect to correct dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] === 'staff') {
        header("Location: staff.php");
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
    <title>Login - GlobeTrek Adventures</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">GlobeTrek<span>.</span></a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="login.php" class="nav-link active">Sign In</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access your tour space</p>
            </div>
            
            <form id="login-form">
                <div class="form-group">
                    <label class="form-label" for="role">Select Your Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="customer">Traveler (Customer)</option>
                        <option value="staff">Agency Staff</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" type="text" id="username" name="username" placeholder="e.g. traveler_srilanka" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button class="btn-cta btn-full btn-loading-action" type="submit" id="btn-login">Sign In</button>
            </form>
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
    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-login');
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            // Loading state
            if (typeof setButtonLoading === 'function') {
                setButtonLoading(btn, 'Signing In...');
            }

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, role })
                });
                const result = await response.json();

                if (result.success) {
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'success');
                    }
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                } else {
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'error');
                    }
                    if (typeof resetButtonLoading === 'function') {
                        resetButtonLoading(btn);
                    }
                }
            } catch (err) {
                if (typeof showToast === 'function') {
                    showToast('Connection error. Please try again.', 'error');
                }
                if (typeof resetButtonLoading === 'function') {
                    resetButtonLoading(btn);
                }
            }
        });
    </script>
</body>
</html>
