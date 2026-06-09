<?php
require_once 'db.php';
$pageTitle = 'Home';

$pets = [];
$catCounts = [];

if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM pets WHERE adoption_status = 'Available' ORDER BY pet_id DESC LIMIT 8");
        $pets = $stmt->fetchAll();

        $catStmt = $pdo->query("SELECT category, COUNT(*) as count FROM pets WHERE adoption_status = 'Available' GROUP BY category");
        while ($row = $catStmt->fetch()) {
            $catCounts[$row['category']] = $row['count'];
        }
    } catch (PDOException $e) {
        // Tables not ready yet
    }
}

require_once 'header.php';
?>

<section class="hero">
    <h1>Find Your New Best Friend</h1>
    <p>Every pet deserves a loving home. Browse our adorable animals waiting for their forever families.</p>
    <div class="search-bar">
        <input type="text" id="searchPets" placeholder="Search by name or breed...">
        <button type="button">Search</button>
    </div>
</section>

<div class="categories">
    <div class="category-card" data-category="Dog">
        <div class="icon">🐕</div>
        <h3>Dogs</h3>
        <p><?php echo isset($catCounts['Dog']) ? $catCounts['Dog'] : 0; ?> available</p>
    </div>
    <div class="category-card" data-category="Puppy">
        <div class="icon">🐶</div>
        <h3>Puppies</h3>
        <p><?php echo isset($catCounts['Puppy']) ? $catCounts['Puppy'] : 0; ?> available</p>
    </div>
    <div class="category-card" data-category="Cat">
        <div class="icon">🐱</div>
        <h3>Cats</h3>
        <p><?php echo isset($catCounts['Cat']) ? $catCounts['Cat'] : 0; ?> available</p>
    </div>
    <div class="category-card" data-category="Kitten">
        <div class="icon">🐱</div>
        <h3>Kittens</h3>
        <p><?php echo isset($catCounts['Kitten']) ? $catCounts['Kitten'] : 0; ?> available</p>
    </div>
</div>

<div class="container">
    <div class="section-title">
        <h2>Available Pets</h2>
        <p>Meet our lovely animals waiting for a home</p>
    </div>
    <?php if (empty($pets)): ?>
        <p style="text-align:center;color:var(--text-light);padding:40px;">No pets available yet. Check back soon!</p>
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
                        </div>
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

<?php require_once 'footer.php'; ?>
