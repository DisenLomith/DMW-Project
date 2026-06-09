<?php
require_once 'db.php';
if (!isset($_SESSION['adopter_id'])) { header('Location: auth.php'); exit(); }
$pageTitle = 'My Dashboard';

$adopter = [];
$myRequests = [];

$adopterId = $_SESSION['adopter_id'];
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM adopters WHERE adopter_id = ?");
        $stmt->execute([$adopterId]);
        $adopter = $stmt->fetch();

        $requests = $pdo->prepare("
            SELECT ar.*, p.pet_name, p.breed, p.category, p.image, p.age, p.gender
            FROM adoption_requests ar
            JOIN pets p ON ar.pet_id = p.pet_id
            WHERE ar.adopter_id = ?
            ORDER BY ar.request_date DESC
        ");
        $requests->execute([$adopterId]);
        $myRequests = $requests->fetchAll();
    } catch (PDOException $e) {
        $adopter = [];
        $myRequests = [];
    }
}

$total = count($myRequests);
$pending = 0; $approved = 0; $rejected = 0;
foreach ($myRequests as $r) {
    if ($r['status'] === 'Pending') $pending++;
    elseif ($r['status'] === 'Approved') $approved++;
    elseif ($r['status'] === 'Rejected') $rejected++;
}

require_once 'header.php';
?>

<div class="dashboard">
    <div class="dashboard-sidebar">
        <div class="user-info">
            <h4><?php echo isset($adopter['first_name']) ? htmlspecialchars($adopter['first_name'] . ' ' . $adopter['last_name']) : 'Adopter'; ?></h4>
            <p><?php echo isset($adopter['email']) ? htmlspecialchars($adopter['email']) : ''; ?></p>
        </div>
        <a href="adopter_dashboard.php" class="active">📊 Overview</a>
        <a href="pets.php">🐾 Browse Pets</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="dashboard-content">
        <h1>My Dashboard</h1>
        <p class="welcome">Track your adoption applications</p>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-number"><?php echo $total; ?></div><div class="stat-label">Total Applications</div></div>
            <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-number"><?php echo $pending; ?></div><div class="stat-label">Pending Review</div></div>
            <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-number"><?php echo $approved; ?></div><div class="stat-label">Approved</div></div>
            <div class="stat-card"><div class="stat-icon">❌</div><div class="stat-number"><?php echo $rejected; ?></div><div class="stat-label">Rejected</div></div>
        </div>

        <h3 style="margin-bottom:16px;">My Adoption Requests</h3>
        <div class="table-container">
            <table>
                <thead><tr><th>Pet</th><th>Category</th><th>Breed</th><th>Age</th><th>Request Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php if (empty($myRequests)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-light);">No applications yet. <a href="pets.php">Browse pets to adopt!</a></td></tr>
                    <?php else: ?>
                        <?php foreach ($myRequests as $req): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($req['pet_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($req['category']); ?></td>
                                <td><?php echo htmlspecialchars($req['breed']); ?></td>
                                <td><?php echo $req['age']; ?> yr</td>
                                <td><?php echo date('Y-m-d', strtotime($req['request_date'])); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($req['status']); ?>"><?php echo $req['status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h3 style="margin:30px 0 16px;">My Profile</h3>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div><strong>Name:</strong> <?php echo isset($adopter['first_name']) ? htmlspecialchars($adopter['first_name'] . ' ' . $adopter['last_name']) : 'N/A'; ?></div>
                <div><strong>NIC:</strong> <?php echo isset($adopter['adopter_nic']) ? htmlspecialchars($adopter['adopter_nic']) : 'N/A'; ?></div>
                <div><strong>Email:</strong> <?php echo isset($adopter['email']) ? htmlspecialchars($adopter['email']) : 'N/A'; ?></div>
                <div><strong>Phone:</strong> <?php echo isset($adopter['phone']) ? htmlspecialchars($adopter['phone']) : 'N/A'; ?></div>
                <div><strong>Address:</strong> <?php echo isset($adopter['address']) ? htmlspecialchars($adopter['address']) : 'N/A'; ?></div>
                <div><strong>Occupation:</strong> <?php echo isset($adopter['occupation']) ? htmlspecialchars($adopter['occupation']) : 'N/A'; ?></div>
                <div><strong>Status:</strong> <?php if (isset($adopter['adopter_status'])): ?><span class="status-badge status-<?php echo strtolower($adopter['adopter_status']); ?>"><?php echo $adopter['adopter_status']; ?></span><?php else: ?>N/A<?php endif; ?></div>
                <div><strong>Registered:</strong> <?php echo isset($adopter['registration_date']) ? date('Y-m-d', strtotime($adopter['registration_date'])) : 'N/A'; ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
