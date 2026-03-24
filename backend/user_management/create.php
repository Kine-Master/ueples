<?php
// backend/user_management/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']); exit;
}

try {
    $role_id    = (int)($_POST['role_id'] ?? 0);
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');

    if (!$role_id || !$username || !$password || !$first_name || !$last_name) {
        throw new Exception("Required fields: role, username, password, first name, last name.");
    }
    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters.");
    }

    // Check valid role
    $stmt = $pdo->prepare("SELECT role_id FROM role WHERE role_id = ?");
    $stmt->execute([$role_id]);
    if (!$stmt->fetch()) throw new Exception("Invalid role selected.");

    // Check username uniqueness
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) throw new Exception("Username '$username' is already taken.");

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO user (role_id, username, password_hash, first_name, middle_name, last_name,
                           email, academic_rank, school_college, department)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $role_id, $username, $hash,
        $first_name, trim($_POST['middle_name'] ?? '') ?: null, $last_name,
        trim($_POST['email'] ?? '') ?: null,
        trim($_POST['academic_rank'] ?? '') ?: null,
        trim($_POST['school_college'] ?? '') ?: null,
        trim($_POST['department'] ?? '') ?: null,
    ]);

    $newId = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_USER', "Created user #$newId: $first_name $last_name ($username)");
    echo json_encode(['status' => 'success', 'user_id' => $newId]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>