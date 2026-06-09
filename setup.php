<?php
/*
Default Admin Account
 */

require_once 'db.php';

// Create default admin if not exists
$check = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
if ($check == 0) {
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin (admin_name, admin_password) VALUES (?, ?)");
    $stmt->execute(['admin', $hashed]);
    echo "✅ Default admin created: username = <strong>admin</strong>, password = <strong>admin123</strong><br>";
} else {
    echo "ℹ️ Admin account already exists.<br>";
}

echo "<br>✅ Setup complete! <a href='index.php'>Go to Home</a>";
