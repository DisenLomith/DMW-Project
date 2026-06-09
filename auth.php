<?php
require_once 'db.php';
if (isset($_SESSION['admin_id'])) { header('Location: admin_dashboard.php'); exit(); }
if (isset($_SESSION['adopter_id'])) { header('Location: index.php'); exit(); }

$pageTitle = 'Login';
require_once 'header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Welcome Back</h2>
        <p class="subtitle auth-subtitle">Sign in to your account</p>

        <form id="loginForm" method="POST">
            <div class="form-group">
                <label>Email or Username</label>
                <input type="text" name="login_identity" placeholder="Enter your email or username" required>
                <div class="error-text">This field is required</div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="login_password" placeholder="Enter your password" required>
                <div class="error-text">This field is required</div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
            <div class="auth-toggle">Don't have an account? <a class="toggle-register">Register here</a></div>
        </form>

        <form id="registerForm" method="POST" style="display:none;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="reg_first_name" placeholder="First name" required>
                    <div class="error-text">Required</div>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="reg_last_name" placeholder="Last name" required>
                    <div class="error-text">Required</div>
                </div>
            </div>
            <div class="form-group">
                <label>NIC Number</label>
                <input type="text" name="reg_nic" placeholder="National ID number" required>
                <div class="error-text">Required</div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="reg_email" placeholder="your@email.com" required>
                <div class="error-text">Valid email required</div>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="reg_phone" placeholder="Phone number" required>
                <div class="error-text">Required</div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="reg_address" placeholder="Your address" required>
                <div class="error-text">Required</div>
            </div>
            <div class="form-group">
                <label>Occupation</label>
                <input type="text" name="reg_occupation" placeholder="Your occupation" required>
                <div class="error-text">Required</div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="reg_password" id="reg_password" placeholder="Min 6 characters" required>
                <div class="error-text">Required</div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="reg_confirm_password" id="reg_confirm_password" placeholder="Confirm password" required>
                <div class="error-text">Required</div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
            <div class="auth-toggle">Already have an account? <a class="toggle-register">Sign in</a></div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
