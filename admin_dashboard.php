<?php
require_once 'db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: auth.php'); exit(); }
$pageTitle = 'Admin Dashboard';

$totalPets = 0; $availablePets = 0; $totalAdopters = 0;
$totalRequests = 0; $pendingRequests = 0; $approvedRequests = 0;
$requests = []; $chartData = [];

if (isset($pdo)) { try {
    $totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
    $availablePets = $pdo->query("SELECT COUNT(*) FROM pets WHERE adoption_status='Available'")->fetchColumn();
    $totalAdopters = $pdo->query("SELECT COUNT(*) FROM adopters")->fetchColumn();
    $totalRequests = $pdo->query("SELECT COUNT(*) FROM adoption_requests")->fetchColumn();
    $pendingRequests = $pdo->query("SELECT COUNT(*) FROM adoption_requests WHERE status='Pending'")->fetchColumn();
    $approvedRequests = $pdo->query("SELECT COUNT(*) FROM adoption_requests WHERE status='Approved'")->fetchColumn();

    $requests = $pdo->query("
        SELECT ar.*, a.first_name, a.last_name, a.email, a.phone, a.address, a.occupation, a.adopter_nic,
               p.pet_name, p.breed, p.category
        FROM adoption_requests ar
        JOIN adopters a ON ar.adopter_id = a.adopter_id
        JOIN pets p ON ar.pet_id = p.pet_id
        ORDER BY ar.request_date DESC
    ")->fetchAll();

    $chartData = $pdo->query("
        SELECT DATE_FORMAT(request_date, '%b') as month, COUNT(*) as count
        FROM adoption_requests
        WHERE request_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY MONTH(request_date)
        ORDER BY request_date ASC
    ")->fetchAll();
} catch (PDOException $e) {
    // Tables may not be ready yet
} }

require_once 'header.php';
?>

<div class="dashboard">
    <div class="dashboard-sidebar">
        <div class="user-info">
            <h4>Admin Panel</h4>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
        </div>
        <a href="admin_dashboard.php" class="active">📊 Dashboard</a>
        <a href="manage_pets.php">🐾 Manage Pets</a>
        <a href="admin_dashboard.php#requests">📋 Requests</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="dashboard-content">
        <h1>Admin Dashboard</h1>
        <p class="welcome">Manage your pet adoption system</p>

        <div class="stats-grid" id="stats">
            <div class="stat-card"><div class="stat-icon">🐾</div><div class="stat-number"><?php echo $totalPets; ?></div><div class="stat-label">Total Pets</div></div>
            <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-number"><?php echo $availablePets; ?></div><div class="stat-label">Available</div></div>
            <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-number"><?php echo $totalAdopters; ?></div><div class="stat-label">Adopters</div></div>
            <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-number"><?php echo $totalRequests; ?></div><div class="stat-label">Total Requests</div></div>
            <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-number"><?php echo $pendingRequests; ?></div><div class="stat-label">Pending</div></div>
            <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-number"><?php echo $approvedRequests; ?></div><div class="stat-label">Approved</div></div>
        </div>

        <div style="margin-bottom:30px;">
            <h3 style="margin-bottom:12px;">Monthly Adoption Requests</h3>
            <div class="chart-container">
                <?php
                $maxCount = 1;
                foreach ($chartData as $d) { if ($d['count'] > $maxCount) $maxCount = $d['count']; }
                foreach ($chartData as $d):
                    $height = ($d['count'] / $maxCount) * 160;
                ?>
                    <div class="chart-bar" style="height: <?php echo $height; ?>px;">
                        <span class="bar-value"><?php echo $d['count']; ?></span>
                        <span class="bar-label"><?php echo $d['month']; ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($chartData)): ?>
                    <p style="color:var(--text-light);width:100%;text-align:center;">No data yet</p>
                <?php endif; ?>
            </div>
        </div>

        <h3 id="requests" style="margin-bottom:16px;">Adoption Requests</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Adopter</th><th>NIC</th><th>Contact</th><th>Occupation</th><th>Pet</th><th>Category</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="10" style="text-align:center;padding:24px;color:var(--text-light);">No adoption requests yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td>#<?php echo $req['request_id']; ?></td>
                                <td><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($req['adopter_nic']); ?></td>
                                <td><?php echo htmlspecialchars($req['email']); ?><br><small><?php echo htmlspecialchars($req['phone']); ?></small></td>
                                <td><?php echo htmlspecialchars($req['occupation']); ?>
                                    <?php if (strtolower($req['occupation']) === 'unemployed'): ?>
                                        <br><small style="color:var(--danger);font-weight:600;">Unemployed</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($req['pet_name']); ?> (<?php echo htmlspecialchars($req['breed']); ?>)</td>
                                <td><?php echo htmlspecialchars($req['category']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($req['request_date'])); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($req['status']); ?>"><?php echo $req['status']; ?></span></td>
                                <td>
                                    <?php if ($req['status'] === 'Pending'): ?>
                                        <div class="actions">
                                            <button class="btn btn-success btn-sm btn-accept" data-id="<?php echo $req['request_id']; ?>">Accept</button>
                                            <button class="btn btn-danger btn-sm btn-reject" data-id="<?php echo $req['request_id']; ?>">Reject</button>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:var(--text-light);font-size:13px;"><?php echo $req['status']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;text-align:right;">
            <a href="process_ajax.php?action=download_excel" class="btn btn-primary">📥 Download as Excel</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
