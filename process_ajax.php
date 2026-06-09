<?php
require_once 'db.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (!isset($pdo)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

switch ($action) {

    // ==================== LOGIN ====================
    case 'login':
        header('Content-Type: application/json');
        $identity = trim($_POST['login_identity'] ?? '');
        $password = $_POST['login_password'] ?? '';
        if (empty($identity) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill all fields.']); exit();
        }
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_name = ?");
            $stmt->execute([$identity]);
            $admin = $stmt->fetch();
            if ($admin && password_verify($password, $admin['admin_password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['admin_name'];
                echo json_encode(['success' => true, 'message' => 'Welcome Admin!', 'redirect' => 'admin_dashboard.php']);
                break;
            }
            $stmt = $pdo->prepare("SELECT * FROM adopters WHERE email = ? OR adopter_nic = ?");
            $stmt->execute([$identity, $identity]);
            $adopter = $stmt->fetch();
            if ($adopter && password_verify($password, $adopter['password'])) {
                $_SESSION['adopter_id'] = $adopter['adopter_id'];
                $_SESSION['adopter_name'] = $adopter['first_name'] . ' ' . $adopter['last_name'];
                echo json_encode(['success' => true, 'message' => 'Welcome ' . $adopter['first_name'] . '!', 'redirect' => 'index.php']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== REGISTER ====================
    case 'register':
        header('Content-Type: application/json');
        $nic = trim($_POST['reg_nic'] ?? '');
        $first_name = trim($_POST['reg_first_name'] ?? '');
        $last_name = trim($_POST['reg_last_name'] ?? '');
        $phone = trim($_POST['reg_phone'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');
        $address = trim($_POST['reg_address'] ?? '');
        $occupation = trim($_POST['reg_occupation'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        if (empty($nic) || empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($address) || empty($occupation) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']); exit();
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']); exit();
        }
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM adopters WHERE email = ? OR adopter_nic = ?");
            $check->execute([$email, $nic]);
            if ($check->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Email or NIC already registered.']); exit();
            }
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO adopters (adopter_nic, first_name, last_name, phone, email, address, occupation, password, registration_date, adopter_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')");
            $stmt->execute([$nic, $first_name, $last_name, $phone, $email, $address, $occupation, $hashed]);
            $_SESSION['adopter_id'] = $pdo->lastInsertId();
            $_SESSION['adopter_name'] = $first_name . ' ' . $last_name;
            echo json_encode(['success' => true, 'message' => 'Registration successful!', 'redirect' => 'index.php']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== ADD PET (CREATE) ====================
    case 'add_pet':
        header('Content-Type: application/json');
        if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit(); }
        $pet_name = trim($_POST['pet_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $breed = trim($_POST['breed'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $vaccination_status = trim($_POST['vaccination_status'] ?? '');
        $arrival_date = $_POST['arrival_date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        if (empty($pet_name) || empty($category) || empty($breed) || empty($gender) || $age < 0 || empty($arrival_date)) {
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']); exit();
        }
        $imagePath = '';
        if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['pet_image']['type'], $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF & WEBP images allowed.']); exit();
            }
            $ext = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('pet_') . '.' . $ext;
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = $uploadDir . $filename;
            } else {
                echo json_encode(['success' => false, 'message' => 'Image upload failed.']); exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Pet image is required.']); exit();
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO pets (category, pet_name, breed, gender, age, vaccination_status, description, arrival_date, adoption_status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Available', ?)");
            $stmt->execute([$category, $pet_name, $breed, $gender, $age, $vaccination_status, $description, $arrival_date, $imagePath]);
            echo json_encode(['success' => true, 'message' => 'Pet added successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add pet: ' . $e->getMessage()]);
        }
        break;

    // ==================== GET PET fill the blanks for get the pets  ====================
    case 'get_pet':
        header('Content-Type: application/json');
        if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit(); }
        $pet_id = intval($_GET['pet_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE pet_id = ?");
            $stmt->execute([$pet_id]);
            $pet = $stmt->fetch();
            echo json_encode($pet ?: ['error' => 'Pet not found']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== UPDATE PET  ====================
    case 'update_pet':
        header('Content-Type: application/json');
        if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit(); }
        $pet_id = intval($_POST['pet_id'] ?? 0);
        $pet_name = trim($_POST['pet_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $breed = trim($_POST['breed'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $vaccination_status = trim($_POST['vaccination_status'] ?? '');
        $arrival_date = $_POST['arrival_date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        try {
            $stmt = $pdo->prepare("SELECT image FROM pets WHERE pet_id = ?");
            $stmt->execute([$pet_id]);
            $current = $stmt->fetch();
            $imagePath = $current['image'] ?? '';
            if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['pet_image']['type'], $allowed)) {
                    $ext = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('pet_') . '.' . $ext;
                    $dest = 'uploads/' . $filename;
                    if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $dest)) {
                        if (!empty($imagePath) && file_exists($imagePath)) unlink($imagePath);
                        $imagePath = $dest;
                    }
                }
            }
            $stmt = $pdo->prepare("UPDATE pets SET category=?, pet_name=?, breed=?, gender=?, age=?, vaccination_status=?, description=?, arrival_date=?, image=? WHERE pet_id=?");
            $stmt->execute([$category, $pet_name, $breed, $gender, $age, $vaccination_status, $description, $arrival_date, $imagePath, $pet_id]);
            echo json_encode(['success' => true, 'message' => 'Pet updated successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== DELETE PET ====================
    case 'delete_pet':
        header('Content-Type: application/json');
        if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit(); }
        $pet_id = intval($_POST['pet_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT image FROM pets WHERE pet_id = ?");
            $stmt->execute([$pet_id]);
            $pet = $stmt->fetch();
            if ($pet) {
                if (!empty($pet['image']) && file_exists($pet['image'])) unlink($pet['image']);
                $pdo->prepare("DELETE FROM adoption_requests WHERE pet_id = ?")->execute([$pet_id]);
                $pdo->prepare("DELETE FROM pets WHERE pet_id = ?")->execute([$pet_id]);
                echo json_encode(['success' => true, 'message' => 'Pet deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pet not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== SUBMIT ADOPTION REQUEST ====================
    case 'submit_adoption':
        header('Content-Type: application/json');
        if (!isset($_SESSION['adopter_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login first.', 'redirect' => 'auth.php']); exit();
        }
        $pet_id = intval($_POST['pet_id'] ?? 0);
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $nic = trim($_POST['nic'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');
        if (empty($pet_id) || empty($first_name) || empty($last_name) || empty($nic) || empty($email) || empty($phone) || empty($address) || empty($occupation)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']); exit();
        }
        try {
            $checkReq = $pdo->prepare("SELECT COUNT(*) FROM adoption_requests WHERE adopter_id = ? AND pet_id = ?");
            $checkReq->execute([$_SESSION['adopter_id'], $pet_id]);
            if ($checkReq->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'You already applied for this pet.']); exit();
            }
            $stmt = $pdo->prepare("INSERT INTO adoption_requests (adopter_id, pet_id, request_date, status) VALUES (?, ?, NOW(), 'Pending')");
            $stmt->execute([$_SESSION['adopter_id'], $pet_id]);
            $pdo->prepare("UPDATE pets SET adoption_status = 'Pending' WHERE pet_id = ?")->execute([$pet_id]);
            if (strtolower($occupation) === 'unemployed') {
                $pdo->prepare("UPDATE adoption_requests SET admin_notes = 'Adopter is unemployed - requires manual review.' WHERE adopter_id = ? AND pet_id = ?")->execute([$_SESSION['adopter_id'], $pet_id]);
            }
            echo json_encode(['success' => true, 'message' => 'Adoption request submitted!', 'redirect' => 'adopter_dashboard.php']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Submission failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== UPDATE REQUEST STATUS (ADMIN) ====================
    case 'update_request':
        header('Content-Type: application/json');
        if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit(); }
        $request_id = intval($_POST['request_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['Approved', 'Rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']); exit();
        }
        try {
            $reqStmt = $pdo->prepare("SELECT * FROM adoption_requests WHERE request_id = ?");
            $reqStmt->execute([$request_id]);
            $request = $reqStmt->fetch();
            if (!$request) { echo json_encode(['success' => false, 'message' => 'Request not found.']); exit(); }
            $pdo->prepare("UPDATE adoption_requests SET status = ? WHERE request_id = ?")->execute([$status, $request_id]);
            if ($status === 'Approved') {
                $pdo->prepare("UPDATE pets SET adoption_status = 'Adopted' WHERE pet_id = ?")->execute([$request['pet_id']]);
            } else {
                $pdo->prepare("UPDATE pets SET adoption_status = 'Available' WHERE pet_id = ?")->execute([$request['pet_id']]);
            }
            echo json_encode(['success' => true, 'message' => 'Request ' . strtolower($status) . ' successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
        }
        break;

    // ==================== DOWNLOAD EXCEL ====================
    case 'download_excel':
        if (!isset($_SESSION['admin_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Unauthorized.']);
            exit();
        }
        try {
            $requests = $pdo->query("
                SELECT ar.request_id, a.first_name, a.last_name, a.adopter_nic, a.email, a.phone, a.occupation,
                       p.pet_name, p.breed, p.category, ar.request_date, ar.status
                FROM adoption_requests ar
                JOIN adopters a ON ar.adopter_id = a.adopter_id
                JOIN pets p ON ar.pet_id = p.pet_id
                ORDER BY ar.request_date DESC
            ")->fetchAll();
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="adoption_requests_' . date('Y-m-d') . '.xls"');
            echo "Request ID\tAdopter Name\tNIC\tEmail\tPhone\tOccupation\tPet Name\tBreed\tCategory\tRequest Date\tStatus\n";
            foreach ($requests as $r) {
                echo implode("\t", [$r['request_id'], $r['first_name'] . ' ' . $r['last_name'], $r['adopter_nic'], $r['email'], $r['phone'], $r['occupation'], $r['pet_name'], $r['breed'], $r['category'], $r['request_date'], $r['status']]) . "\n";
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
        }
        exit();

    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
