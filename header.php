<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Paws&Hearts</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">
        <img src="assets/logo.jpeg" alt="Paws&Hearts">
        <span>Paws&Hearts</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="pets.php">Pets</a></li>
        <?php if (isset($_SESSION['adopter_id'])): ?>
            <li><a href="adopter_dashboard.php">My Dashboard</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['admin_id'])): ?>
            <li><a href="admin_dashboard.php">Admin Panel</a></li>
            <li><a href="manage_pets.php">Manage Pets</a></li>
        <?php endif; ?>
    </ul>
    <div class="nav-actions">
        <button class="dark-toggle" title="Toggle Dark Mode">🌙</button>
        <?php if (isset($_SESSION['admin_id']) || isset($_SESSION['adopter_id'])): ?>
            <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
        <?php else: ?>
            <a href="auth.php" class="btn btn-primary btn-sm">Login</a>
        <?php endif; ?>
    </div>
</nav>

<main>
