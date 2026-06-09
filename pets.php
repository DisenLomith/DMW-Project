<?php
require_once 'db.php';
$pageTitle = 'Browse Pets';

$pets = [];
$category = isset($_GET['category']) ? $_GET['category'] : '';
if (isset($pdo)) {
    try {
        if ($category) {
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE category = ? ORDER BY pet_id DESC");
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query("SELECT * FROM pets ORDER BY pet_id DESC");
        }
        $pets = $stmt->fetchAll();
    } catch (PDOException $e) {
        $pets = [];
    }
}

require_once 'header.php';
?>

<section class="page-banner">
    <h1>Our Lovely Pets</h1>
    <p>Find your perfect companion</p>
</section>

<div class="container">
    <div class="filter-tabs">
        <button class="filter-tab <?php echo !$category ? 'active' : ''; ?>" data-category="all">All</button>
        <button class="filter-tab <?php echo $category === 'Dog' ? 'active' : ''; ?>" data-category="Dog">🐕 Dogs</button>
        <button class="filter-tab <?php echo $category === 'Puppy' ? 'active' : ''; ?>" data-category="Puppy">🐶 Puppies</button>
        <button class="filter-tab <?php echo $category === 'Cat' ? 'active' : ''; ?>" data-category="Cat">🐱 Cats</button>
        <button class="filter-tab <?php echo $category === 'Kitten' ? 'active' : ''; ?>" data-category="Kitten">🐱 Kittens</button>
    </div>

    <div style="text-align:center;margin-bottom:24px;">
        <input type="text" id="searchPets" placeholder="Search by name or breed..." style="padding:10px 20px;border-radius:30px;border:1px solid var(--input-border);background:var(--input-bg);color:var(--text);width:100%;max-width:400px;outline:none;">
    </div>

    <?php if (empty($pets)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--text-light);">
            <div style="font-size:64px;margin-bottom:16px;">🐾</div>
            <h3>No pets found</h3>
            <p>Check back later for new arrivals!</p>
        </div>
    <?php else: ?>
        <div class="pet-grid">
            <?php foreach ($pets as $pet): ?>
                <div class="pet-card" data-category="<?php echo htmlspecialchars($pet['category']); ?>" data-name="<?php echo htmlspecialchars($pet['pet_name']); ?>" data-breed="<?php echo htmlspecialchars($pet['breed']); ?>">
                    <?php if (!empty($pet['image']) && file_exists($pet['image'])): ?>
                        <img class="pet-image" src="<?php echo htmlspecialchars($pet['image']); ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>">
                    <?php else: ?>
                        <div class="pet-image-placeholder">🐾</div>
                    <?php endif; ?>
                    <div class="pet-info">
                        <h3><?php echo htmlspecialchars($pet['pet_name']); ?></h3>
                        <div class="breed"><?php echo htmlspecialchars($pet['breed']); ?></div>
                        <div class="meta">
                            <span>🎂 <?php echo $pet['age']; ?> yr</span>
                            <span>⚤ <?php echo $pet['gender']; ?></span>
                            <span>💉 <?php echo htmlspecialchars($pet['vaccination_status']); ?></span>
                        </div>
                        <p style="font-size:13px;color:var(--text-light);margin-bottom:8px;"><?php echo nl2br(htmlspecialchars(substr($pet['description'], 0, 100))); ?>...</p>
                        <span class="status-badge status-<?php echo strtolower($pet['adoption_status']); ?>"><?php echo $pet['adoption_status']; ?></span>
                        <?php if (isset($_SESSION['adopter_id'])): ?>
                            <button class="btn btn-primary btn-sm btn-adopt" style="margin-top:12px;width:100%;" data-pet-id="<?php echo $pet['pet_id']; ?>" data-pet-name="<?php echo htmlspecialchars($pet['pet_name']); ?>">Adopt Me</button>
                        <?php elseif (!isset($_SESSION['admin_id'])): ?>
                            <a href="auth.php" class="btn btn-outline btn-sm" style="margin-top:12px;width:100%;text-align:center;">Login to Adopt</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal" id="adoptModal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h3 class="modal-title">Adopt a Pet</h3>
        <form id="adoptForm">
            <input type="hidden" name="pet_id" id="adoptPetId">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>
            </div>
            <div class="form-group">
                <label>NIC Number</label>
                <input type="text" name="nic" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" required>
            </div>
            <div class="form-group">
                <label>Occupation</label>
                <input type="text" name="occupation" required>
            </div>
            <div class="form-group">
                <label>Why do you want to adopt this pet?</label>
                <textarea name="reason" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Submit Request</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>